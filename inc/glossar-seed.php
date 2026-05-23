<?php
/**
 * Glossar- & Essay-Seed — Hasimuener Journal
 *
 * Idempotentes Anlegen des Essays „Sterblichkeit ist kein Softwarefehler"
 * sowie der zugehörigen Glossar-Begriffe. Prüft pro Slug, ob ein Eintrag
 * bereits existiert; bestehende Essays werden bei Versionswechsel über
 * `_hp_essay_content_version` aktualisiert. Enthält außerdem einen
 * einmaligen Cleanup für den abgelösten Essay „Abrechnung mit dem
 * Transhumanismus".
 *
 * Auslöser: einmaliger Lauf pro `HP_GLOSSAR_SEED_VERSION` im
 * Admin-Kontext, gesteuert über die Option `hp_glossar_seed_version`.
 *
 * @package Hasimuener_Journal
 * @since   7.0.0
 */

defined( 'ABSPATH' ) || exit;

const HP_GLOSSAR_SEED_VERSION = '2026-05-23-dedupe-glossar';

function hp_run_glossar_seed_once(): void {
	if ( ! is_admin() ) {
		return;
	}

	if ( get_option( 'hp_glossar_seed_version' ) === HP_GLOSSAR_SEED_VERSION ) {
		return;
	}

	hp_remove_abrechnung_transhumanismus_essay();
	hp_glossar_dedupe_once();
	hp_seed_perspektive_glossary();
	hp_seed_sterblichkeit_essay();
	hp_seed_sterblichkeit_glossary();

	update_option( 'hp_glossar_seed_version', HP_GLOSSAR_SEED_VERSION, false );
}
add_action( 'admin_init', 'hp_run_glossar_seed_once', 25 );

/**
 * Sucht einen Glossar-Eintrag per Slug — über alle relevanten Stati.
 *
 * `get_page_by_path()` filtert per Default auf `publish` und übersieht
 * dadurch Drafts/Trash, was den Seeder zwingt, denselben Begriff erneut
 * anzulegen und Slug-Suffixe (`begriff-2`) zu vergeben.
 *
 * @param string $slug Glossar-Slug.
 * @return WP_Post|null
 */
function hp_glossar_find_by_slug( string $slug ): ?WP_Post {
	$post = get_page_by_path(
		$slug,
		OBJECT,
		'glossar',
		[ 'publish', 'draft', 'pending', 'private', 'future' ]
	);

	return $post instanceof WP_Post ? $post : null;
}

/**
 * Einmaliger Cleanup: entfernt Duplikate aus dem Glossar.
 *
 * Strategie:
 * - Alle Glossar-Posts in allen relevanten Stati laden.
 * - Nach normalisiertem Titel gruppieren (case-insensitive, Whitespace-getrimmt).
 * - Pro Gruppe einen "Keeper" wählen — Reihenfolge:
 *     1. Status `publish`
 *     2. Slug ohne `-N`-Suffix
 *     3. älteste Post-ID
 * - Alle übrigen Duplikate via `wp_delete_post( ..., true )` hart löschen
 *   (umgeht den Trash, da der Slug sonst weiter blockiert wäre).
 * - Hat der Keeper einen Slug-Suffix und der kanonische Slug ist nach
 *   dem Löschen frei: Slug auf die Basis zurücksetzen.
 *
 * Sicher gegen Re-Run: wird über `HP_GLOSSAR_SEED_VERSION` einmalig
 * ausgeführt.
 */
function hp_glossar_dedupe_once(): void {
	$posts = get_posts(
		[
			'post_type'   => 'glossar',
			'post_status' => [ 'publish', 'draft', 'pending', 'private', 'future' ],
			'numberposts' => -1,
			'orderby'     => 'ID',
			'order'       => 'ASC',
		]
	);

	if ( count( $posts ) < 2 ) {
		return;
	}

	$by_title = [];
	foreach ( $posts as $p ) {
		$key = mb_strtolower( trim( (string) preg_replace( '/\s+/u', ' ', $p->post_title ) ) );
		if ( '' === $key ) {
			continue;
		}
		$by_title[ $key ][] = $p;
	}

	foreach ( $by_title as $group ) {
		if ( count( $group ) < 2 ) {
			continue;
		}

		usort( $group, static function ( $a, $b ) {
			$a_pub = 'publish' === $a->post_status ? 0 : 1;
			$b_pub = 'publish' === $b->post_status ? 0 : 1;
			if ( $a_pub !== $b_pub ) {
				return $a_pub - $b_pub;
			}

			$a_suffix = preg_match( '/-\d+$/', $a->post_name ) ? 1 : 0;
			$b_suffix = preg_match( '/-\d+$/', $b->post_name ) ? 1 : 0;
			if ( $a_suffix !== $b_suffix ) {
				return $a_suffix - $b_suffix;
			}

			return (int) $a->ID - (int) $b->ID;
		} );

		$keep = array_shift( $group );

		foreach ( $group as $dup ) {
			wp_delete_post( (int) $dup->ID, true );
		}

		// Keeper-Slug von Suffix befreien, wenn der kanonische Slug nun frei ist
		if ( preg_match( '/^(.*)-\d+$/', $keep->post_name, $m ) ) {
			$base       = $m[1];
			$still_used = hp_glossar_find_by_slug( $base );
			if ( ! $still_used ) {
				wp_update_post(
					[
						'ID'        => (int) $keep->ID,
						'post_name' => $base,
					]
				);
			}
		}
	}
}

/**
 * Einmaliger Cleanup: löscht den abgelösten Essay „Abrechnung mit dem
 * Transhumanismus" (Slug: abrechnung-transhumanismus) endgültig aus der
 * Datenbank, inklusive aller Revisions und Meta-Einträge.
 *
 * Wird über den Seed-Versions-Flag genau einmal pro Bump ausgeführt.
 */
function hp_remove_abrechnung_transhumanismus_essay(): void {
	$slug = 'abrechnung-transhumanismus';

	$existing = get_page_by_path( $slug, OBJECT, 'essay' );
	if ( ! $existing instanceof WP_Post ) {
		return;
	}

	wp_delete_post( $existing->ID, true );
}


/**
 * Seedet die neun Glossar-Begriffe des Transhumanismus-Komplexes.
 */
function hp_seed_perspektive_glossary(): void {
	$entries = [
		[
			'slug'  => 'transhumanismus',
			'title' => 'Transhumanismus',
			'kurz'  => 'Eine im Silicon Valley verwurzelte ideologische Bewegung, die die Überwindung der biologischen Grenzen des Menschen (Altern, Tod) durch Technologie anstrebt. Agiert als säkulare Ersatzreligion für eine hyperreiche Elite zur Betäubung von existenzieller Verlustangst.',
		],
		[
			'slug'  => 'reduktionismus-methodischer',
			'title' => 'Reduktionismus (methodischer)',
			'kurz'  => 'Der fundamentale Fehler der modernen westlichen Wissenschaft seit Descartes, das Lebendige und das Bewusstsein rein als Summe mechanischer, berechenbarer Einzelteile zu betrachten. Ignoriert die holistische Komplexität biologischer und kosmischer Systeme.',
		],
		[
			'slug'  => 'biophobie',
			'title' => 'Biophobie',
			'kurz'  => 'Die pathologische Angst vor der Unberechenbarkeit, Vergänglichkeit und Fleischlichkeit des organischen Lebens, die sich im transhumanistischen Drang äußert, alles Lebendige in sterile, kontrollierbare Datensätze (Silizium) zu pressen.',
		],
		[
			'slug'  => 'algorithmische-oeffentlichkeit',
			'title' => 'Algorithmische Öffentlichkeit',
			'kurz'  => 'Ein durch Plattform-Kapitalismus deformierter digitaler Raum, in dem Aufmerksamkeitsökonomie und automatisierte Erregungsschleifen die Fragmentierung des gesellschaftlichen Diskurses forcieren. Sie entzieht dem Individuum die freie Urteilskraft und ersetzt Verständigung durch algorithmisch belohntes Lagerdenken.',
		],
		[
			'slug'  => 'kosmotechnik',
			'title' => 'Kosmotechnik',
			'kurz'  => 'Ein von Yuk Hui geprägter Begriff. Der Gegenentwurf zur linearen, westlichen Zerstörungstechnologie. Er fordert, dass technische Entwicklungen wieder an die jeweilige Kultur, Natur und kosmische Ordnung zurückgebunden werden, statt das Universum als tote Rechenmasse auszubeuten.',
		],
		[
			'slug'  => 'kommune-grundzelle',
			'title' => 'Kommune (als Grundzelle)',
			'kurz'  => 'Die unteilbare Basiseinheit dezentraler gesellschaftlicher Selbstorganisation. Sie bildet das Fundament, aus dem sich alle nachgelagerten föderativen Fach- und Ratsstrukturen ableiten, um hierarchische Machtkonzentrationen systemisch zu verhindern.',
		],
		[
			'slug'  => 'foederative-dacharchitektur',
			'title' => 'Föderative Dacharchitektur',
			'kurz'  => 'Ein horizontales Organisationsmodell, bei dem die koordinierende Instanz (der Rat) nicht als legislative Herrschaftsebene operiert, sondern strikt als dezentraler Dienstleister und kollektives Sprachrohr der angegliederten Basiseinheiten fungiert.',
		],
		[
			'slug'  => 'schicht-modell-infrastruktur-schichten',
			'title' => 'Schicht-Modell (Infrastruktur-Schichten)',
			'kurz'  => 'Die strikte architektonische Trennung zwischen unveränderlicher Basisinfrastruktur (Schicht 1) und flexiblen, funktionalen Anwendungsebenen (Schicht 2+). Garantiert im digitalen wie im gesellschaftlichen Kontext die dauerhafte Autonomie des Systems gegenüber dem Zugriff temporärer Akteure.',
		],
		[
			'slug'  => 'jin-jiyan-azadi',
			'title' => 'Jin, Jiyan, Azadî (Frau, Leben, Freiheit)',
			'kurz'  => 'Das existenzielle Kern-Narrativ emanzipatorischer Bewegungen im kurdischen Kontext. Es beschreibt die untrennbare dialektische Verknüpfung von Befreiung, organischem Leben und radikaler gesellschaftlicher Transformation abseits staatlicher Machtstrukturen.',
		],
	];

	foreach ( $entries as $entry ) {
		$existing = hp_glossar_find_by_slug( $entry['slug'] );
		if ( $existing instanceof WP_Post ) {
			continue;
		}

		$body = "<!-- wp:paragraph -->\n<p>" . esc_html( $entry['kurz'] ) . "</p>\n<!-- /wp:paragraph -->";

		$post_id = wp_insert_post( [
			'post_type'    => 'glossar',
			'post_status'  => 'publish',
			'post_name'    => $entry['slug'],
			'post_title'   => $entry['title'],
			'post_excerpt' => $entry['kurz'],
			'post_content' => $body,
		], true );

		if ( is_wp_error( $post_id ) || ! $post_id ) {
			continue;
		}

		update_post_meta( $post_id, '_hp_glossar_kurz', $entry['kurz'] );
	}
}

/**
 * Legt den Essay „Sterblichkeit ist kein Softwarefehler" idempotent an.
 */
function hp_seed_sterblichkeit_essay(): void {
	$slug            = 'sterblichkeit-kein-softwarefehler';
	$content_version = 'r4-quellen-aufklappbar';
	$title           = 'Sterblichkeit ist kein Softwarefehler';
	$excerpt         = 'Milliarden fließen in die Abschaffung des Todes. Das ist kein Fortschritt, sondern eine Flucht – und der Mensch wird nicht gerettet, indem man ihn abschafft.';

	$content = hp_get_sterblichkeit_essay_content();

	$existing = get_page_by_path( $slug, OBJECT, 'essay' );
	if ( $existing instanceof WP_Post ) {
		if ( get_post_meta( $existing->ID, '_hp_essay_content_version', true ) !== $content_version ) {
			wp_update_post( [
				'ID'           => $existing->ID,
				'post_title'   => $title,
				'post_excerpt' => $excerpt,
				'post_content' => $content,
			] );
			update_post_meta( $existing->ID, '_hp_meta_description', $excerpt );
			update_post_meta( $existing->ID, '_hp_reading_time', 22 );
			update_post_meta( $existing->ID, '_hp_reading_minutes', 22 );
			update_post_meta( $existing->ID, '_hp_essay_content_version', $content_version );
		}
		return;
	}

	$post_id = wp_insert_post( [
		'post_type'    => 'essay',
		'post_status'  => 'publish',
		'post_name'    => $slug,
		'post_title'   => $title,
		'post_excerpt' => $excerpt,
		'post_content' => $content,
	], true );

	if ( is_wp_error( $post_id ) || ! $post_id ) {
		return;
	}

	update_post_meta( $post_id, '_hp_meta_description', $excerpt );
	update_post_meta( $post_id, '_hp_reading_time', 22 );
	update_post_meta( $post_id, '_hp_reading_minutes', 22 );
	update_post_meta( $post_id, '_hp_essay_content_version', $content_version );

	if ( taxonomy_exists( 'topic' ) ) {
		wp_set_object_terms( $post_id, [ 'Gesellschaft', 'Technologie', 'Philosophie' ], 'topic', false );
	}
}

/**
 * Liefert den HTML-Body des Essays „Sterblichkeit ist kein Softwarefehler"
 * als Gutenberg-Blockfolge mit interner Verlinkung in den Wissensgraphen
 * sowie einem abschließenden Quellenapparat.
 */
function hp_get_sterblichkeit_essay_content(): string {
	$lead = 'Milliarden fließen in die Abschaffung des Todes. Das ist kein Fortschritt, sondern eine Flucht – und der Mensch wird nicht gerettet, indem man ihn abschafft.';

	$intro = [
		'<p>Der <a href="/glossar/transhumanismus/">Transhumanismus</a> tritt selten als Religion auf. Er spricht die Sprache der Forschung, der Innovation, der Effizienz. Er verspricht Heilung, längeres Leben, höhere Intelligenz, bessere Körper, perfektere Entscheidungen, vielleicht eines Tages die Überwindung des Todes. Wer wollte dagegen sein? Wer wollte Krankheit, Demenz, körperlichen Verfall oder frühes Sterben verteidigen?</p>',
		'<p>Genau hier beginnt seine Verführung.</p>',
		'<p>Denn der stärkste Teil des transhumanistischen Versprechens ist nicht falsch. Es ist gut, Leiden zu lindern. Es ist gut, Krankheiten zu behandeln, Prothesen zu verbessern, blinden Menschen Sehen zu ermöglichen, gelähmten Menschen Bewegung zurückzugeben, Alterungsprozesse besser zu verstehen. Eine Kritik des Transhumanismus darf deshalb nicht mit Fortschrittsfeindschaft verwechselt werden. Nicht jede Form technologischer Erweiterung ist Entmenschlichung. Nicht jede Forschung an Langlebigkeit ist Hybris. Nicht jeder medizinische Eingriff ist ein Angriff auf das Wesen des Menschen.</p>',
		'<p>Der Bruch beginnt an einer anderen Stelle. Er beginnt dort, wo Heilung zur Erlösungsfantasie wird. Dort, wo der Mensch nicht mehr als lebendiges, verletzliches, soziales, sterbliches Wesen verstanden wird, sondern als defektes System. Als schlecht optimierte Maschine. Als biologischer Altbau. Dort, wo der Körper nicht mehr Heimat ist, sondern Hindernis. Dort, wo Sterblichkeit nicht mehr zur menschlichen Existenz gehört, sondern als technisches Problem behandelt wird, das nur noch auf seine Lösung wartet.</p>',
		'<p>Transhumanismus wird gefährlich, sobald er aus Medizin eine Metaphysik macht.</p>',
	];

	$sections = [
		'Der Mensch als fehlerhafte Maschine' => [
			'<p>Dass dies kein Strohmann ist, lässt sich an Bilanzen ablesen. Altos Labs startete 2022 mit drei Milliarden Dollar Anschubfinanzierung — der bestfinanzierte Biotech-Start der Geschichte, gestützt unter anderem von Jeff Bezos. Retro Biosciences, finanziert vom OpenAI-Chef Sam Altman, sammelt Kapital für eine Bewertung von rund fünf Milliarden Dollar ein, ohne eine einzige klinische Datenreihe vorweisen zu können; das erklärte Ziel ist, dem Menschen zehn Jahre hinzuzufügen. Peter Thiels Methuselah-Stiftung will „90 zum neuen 50" machen — bis 2030. Das Vokabular dieser Projekte ist nicht das der Heilung, sondern das der Versionierung. Der Körper erscheint als veraltete Ausgabe, der ein Update fehlt.</p>',
			'<p>Das eigentliche Problem liegt nicht in einzelnen Werkzeugen. Es liegt im Menschenbild, das diese Werkzeuge begleitet. Dieses Bild beruht auf einem <a href="/glossar/reduktionismus-methodischer/">methodischen Reduktionismus</a>: Bewusstsein wird als Datenverarbeitung verstanden, Körper als Hardware, Erinnerung als Speicher, Intelligenz als Rechenleistung, Identität als Muster, Leben als Optimierungsproblem. Was sich nicht messen, modellieren oder beschleunigen lässt, erscheint zweitrangig, irrational, veraltet.</p>',
			'<p>Aber der Mensch ist keine Excel-Tabelle mit Stoffwechsel.</p>',
			'<p>Der Reduktionismus gibt sich als Wissenschaft, ist aber eine Setzung. Seit der Philosoph David Chalmers 1995 das „harte Problem des Bewusstseins" formulierte, ist eine Frage offengeblieben, die keine Hirnscan-Auflösung schließt: Warum geht Informationsverarbeitung überhaupt mit subjektivem Erleben einher? Niemand weiß es. Der Transhumanismus überspringt diese Lücke nicht — er verhält sich, als gäbe es sie nicht. Er erklärt für gelöst, was bisher niemand gelöst hat.</p>',
			'<p>Das anschaulichste Maß für diesen Abstand ist kein Argument, sondern ein Wurm. Das Forschungsprojekt OpenWorm versucht seit über einem Jahrzehnt, das Nervensystem des Fadenwurms <em>Caenorhabditis elegans</em> vollständig digital nachzubilden — ganze 302 Neuronen, jede Verbindung kartiert und bekannt. Es ist bis heute nicht abschließend gelungen, dieses Nervensystem so zu emulieren, dass sich der Wurm verhält wie sein lebendiges Vorbild. 302 Neuronen. Das menschliche Gehirn hat rund 86 Milliarden. Wer einen Wurm nicht hochladen kann, sollte vom Upload des Menschen schweigen.</p>',
			'<p>Ein Mensch ist nicht bloß eine Ansammlung von Funktionen. Er ist nicht nur sein Gehirn, nicht nur sein Genom, nicht nur seine Produktivität, nicht nur sein kognitives Profil. Menschliches Leben entsteht aus Körper, Sprache, Erinnerung, Beziehung, Schmerz, Begehren, Angst, Geschichte, Kultur, Endlichkeit und Sinn. Es ist nicht Information, die zufällig auf biologischem Trägermaterial läuft.</p>',
			'<p>Genau diese Verwechslung ist zentral: Das Lebendige wird behandelt, als sei es im Kern bereits maschinell. Die Maschine erscheint dann nicht mehr als Werkzeug des Menschen, sondern als dessen bessere Version. Das Organische wird zum Provisorium erklärt, das Digitale zur Verheißung.</p>',
			'<p>Dabei ist eine Maschine immer nur in Teilbereichen überlegen. Sie rechnet schneller, sortiert präziser, erkennt Muster, erzeugt Texte. Aber aus funktionaler Überlegenheit folgt kein existenzieller Vorrang. Ein Taschenrechner ist besser im Rechnen als ein Kind. Trotzdem ist das Kind nicht die minderwertige Version des Taschenrechners. Der Mensch ist nicht deshalb wertvoll, weil er effizient ist. Er ist wertvoll, bevor er überhaupt etwas leistet.</p>',
		],
		'Die Ersatzreligion der Kontrolle' => [
			'<p>Der moderne Transhumanismus gibt sich nüchtern, aber sein innerer Antrieb ist religiöser, als seine Anhänger zugeben würden. Er verspricht, was Religionen immer versprochen haben: Erlösung vom Leiden, Überwindung der Begrenzung, Rettung vor dem Tod, Fortexistenz über den Zerfall des Körpers hinaus.</p>',
			'<p>Nur sind die alten Symbole ersetzt worden. Aus Seele wird Information. Aus Auferstehung wird Upload. Aus Paradies wird Simulation. Aus Askese wird Selbstoptimierung. Aus Gott wird Technik. Aus Erlösung wird Produktentwicklung.</p>',
			'<p>Es ist kein Zufall, dass diese Bewegung Prognose von Erweckung schwer unterscheidet. Ray Kurzweil, der bekannteste Prophet des Transhumanismus, terminiert die „Singularität" auf das Jahr 2045 und das Erreichen der Langlebigkeits-Fluchtgeschwindigkeit auf etwa 2030 — Daten so präzise wie die einer Wiederkunft. Eine Vorhersage, die ein Datum nennt, ohne einen Mechanismus zu nennen, ist kein Forschungsergebnis. Sie ist ein Glaubensbekenntnis im Tonfall der Ingenieurskunst.</p>',
			'<p>Dahinter steht eine Angst, die selten offen ausgesprochen wird: die Angst vor dem Unverfügbaren. Vor dem Körper. Vor Alterung. Vor Krankheit. Vor Abhängigkeit. Vor Kontrollverlust. Vor dem Tod.</p>',
			'<p>Man kann diese Angst <a href="/glossar/biophobie/">Biophobie</a> nennen — nicht im Sinne eines bloßen Ekels vor Leben, sondern, in der Tradition von Erich Fromms Unterscheidung zwischen Biophilie und Nekrophilie, als tiefe Abwehr gegen das Unberechenbare, Fleischliche, Endliche und Widersprüchliche des organischen Daseins. Das Lebendige soll in kontrollierbare Datensätze übersetzt werden, damit es endlich berechenbar wird.</p>',
			'<p>Das macht den Transhumanismus nicht automatisch falsch. Aber es macht ihn unehrlich, wenn er so tut, als sei er bloß Wissenschaft. Wissenschaft beschreibt, prüft, verwirft, korrigiert. Ideologie verspricht eine Richtung der Geschichte. Der Transhumanismus kippt genau dort in Ideologie, wo er nicht mehr fragt, was Technik kann, sondern behauptet, wohin der Mensch sich entwickeln müsse.</p>',
			'<p>Darin liegt sein autoritärer Kern. Wer den Menschen als Mängelwesen definiert, braucht irgendwann Instanzen, die festlegen, welche Mängel beseitigt werden sollen. Wer Optimierung zum Ziel erklärt, muss bestimmen, was als besser gilt. Solche Fragen sind nie rein technisch. Sie sind politisch, ethisch und sozial. Und sie sind gefährlich, wenn sie von denen beantwortet werden, die ohnehin schon über Kapital, Infrastruktur und Deutungsmacht verfügen.</p>',
		],
		'Die Klassenfrage der Optimierung' => [
			'<p>Die transhumanistische Zukunft wird gerne als Menschheitsprojekt verkauft. Die Gegenwart sagt etwas anderes. Technologische Revolutionen beginnen dort, wo Geld, Labore, Patente und Zugang konzentriert sind — und das ist messbar.</p>',
			'<p>Schon heute, ganz ohne Enhancement, ist Lebenszeit eine Funktion des Einkommens. Eine der größten Studien dazu, von Raj Chetty und Kollegen 2016 im <em>Journal of the American Medical Association</em> veröffentlicht und auf 1,4 Milliarden Steuerdatensätzen beruhend, zeigt: Zwischen dem reichsten und dem ärmsten Prozent der US-Bevölkerung klafft eine Lücke in der Lebenserwartung von fünfzehn Jahren bei Männern und zehn Jahren bei Frauen. Und sie wächst. Zwischen 2001 und 2014 gewannen die obersten fünf Prozent rund zweieinhalb bis drei Jahre Lebenserwartung hinzu. Die untersten fünf Prozent gewannen 0,3 Jahre — oder nichts.</p>',
			'<p>Das ist der Boden, auf den die Versprechen treffen. Eine Technologie, die Lebenszeit verlängert, verteilt sie nicht um. Sie legt sich auf ein bestehendes Gefälle und macht es steiler. Werden biologische, kognitive oder digitale <a href="/glossar/enhancement-technologien/">Erweiterungen</a> marktförmig organisiert, entsteht nicht die befreite Menschheit, sondern eine neue Klassengesellschaft: oben, wer sich Zugriff auf Optimierung kaufen kann; unten, wessen Körper weiterhin verschleißen, wessen Aufmerksamkeit ausgebeutet, wessen Daten geerntet werden.</p>',
			'<p>Deshalb ist die Machtfrage unausweichlich: Wer wird optimiert? Wer bleibt zurück? Wer besitzt die Infrastruktur? Wer kontrolliert die Daten? Wer bestimmt die Norm? Wer entscheidet, welche Körper als reparaturbedürftig gelten und welche als überlegen?</p>',
			'<p>Der Transhumanismus spricht vom Menschen der Zukunft, während die Gegenwart zerfällt. Er träumt von digitaler Unsterblichkeit, während Bildungssysteme ausbluten, Pflegekräfte kollabieren, Kinder in algorithmischen Reizmaschinen aufwachsen, psychische Erkrankungen zunehmen, Demokratien unter Plattformlogiken leiden und ökologische Grenzen ignoriert bleiben.</p>',
			'<p>Hier berührt er die <a href="/glossar/algorithmische-oeffentlichkeit/">algorithmische Öffentlichkeit</a>: eine Öffentlichkeit, in der Aufmerksamkeit nicht mehr frei entsteht, sondern durch Rankings, Feeds, Empfehlungslogiken und automatisierte Verstärkung geformt wird. Diese Systeme belohnen Affekte, verstärken Fragmentierung, schwächen Urteilskraft. So entsteht eine paradoxe Lage: Während oben von Bewusstseinserweiterung, Langlebigkeit und technischer Evolution gesprochen wird, wird unten die alltägliche Aufmerksamkeit zerlegt. Die Zukunft wird optimiert, während die Gegenwart zerstreut wird.</p>',
			'<p>Die Flucht in eine fantastische Zukunft entlastet von der Reparatur der konkreten Gegenwart. Wer vom Upload des Bewusstseins träumt, muss sich weniger mit der Einsamkeit alter Menschen beschäftigen. Wer vom optimierten Körper schwärmt, muss weniger über Arbeitsbedingungen sprechen, die Körper zerstören. Die große Obszönität des Transhumanismus liegt darin, dass er das Falsche zuerst will.</p>',
		],
		'Digitale Unsterblichkeit ist keine Unsterblichkeit' => [
			'<p>Am deutlichsten wird der Denkfehler beim Traum vom <a href="/glossar/mind-uploading/">Mind Uploading</a>. Die Idee klingt spektakulär: Das Gehirn wird kartiert, Bewusstsein rekonstruiert, Persönlichkeit digitalisiert, der Mensch lebt als Informationsmuster weiter.</p>',
			'<p>Aber selbst wenn man eines Tages eine perfekte digitale Kopie eines Menschen erzeugen könnte, bliebe die entscheidende Frage offen: Warum sollte diese Kopie ich sein?</p>',
			'<p>Der Philosoph Derek Parfit hat dieses Problem 1984 in <em>Reasons and Persons</em> mit dem Gedankenexperiment der Teletransportation geschärft. Ein Gerät scannt einen Menschen, zerstört das Original und baut an einem anderen Ort eine atomgenaue Kopie. Die Kopie erinnert sich an alles, hält sich für dieselbe Person — und ist es doch nicht im Sinne numerischer Identität: Bliebe das Original am Leben, stünden sich zwei Menschen gegenüber, nicht einer. Ähnlichkeit ist keine Kontinuität. Simulation ist keine Erfahrung. Ein digitales Modell meiner Person wäre vielleicht ein beeindruckendes Archiv, ein interaktives Denkmal, eine perfekte Täuschung für andere. Aber es wäre nicht die Fortsetzung meines gelebten Bewusstseins.</p>',
			'<p>Wie weit diese Logik führt, zeigt das Startup Nectome, das Gehirnkonservierung für ein späteres Upload anbot — mit einem Verfahren, das den Tod des Kunden voraussetzt: Konserviert werden kann nur ein Gehirn, dessen Träger dafür stirbt. Das MIT beendete 2018 seine Zusammenarbeit mit dem Unternehmen. Deutlicher lässt sich der Widerspruch kaum fassen — eine Unsterblichkeitstechnik, deren erster Schritt das Sterben ist.</p>',
			'<p>Der Tod wird dadurch nicht überwunden. Er wird nur ästhetisch kaschiert. Digitale Unsterblichkeit ist keine Auferstehung. Sie ist Nachlassverwaltung mit Benutzeroberfläche. Der Wunsch, weiterzuleben, ist menschlich. Aber gerade deshalb ist es gefährlich, diese Angst in ein Geschäftsmodell zu verwandeln. Wer Menschen digitale Fortexistenz verkauft, verkauft Trost. Und Trost ist einer der empfindlichsten Märkte überhaupt.</p>',
		],
		'Endlichkeit als Bedingung von Sinn' => [
			'<p>Der Transhumanismus behandelt Sterblichkeit als Niederlage. Vielleicht ist gerade das sein tiefster Irrtum.</p>',
			'<p>Der Philosoph Bernard Williams hat 1973 in seinem Essay über den „Fall Makropulos" eine unbequeme These formuliert: Ein unendliches Leben wäre nicht erstrebenswert, sondern unerträglich. Williams\' Figur, Elina Makropulos, lebt durch ein Elixier dreihundert Jahre im biologischen Alter von zweiundvierzig — und erstarrt in Kälte, Langeweile, Gleichgültigkeit. Sein Argument: Was unsere Wünsche und Bindungen überhaupt mit Bedeutung auflädt, ist ihre Verknüpfung mit einem endlichen Leben. Eine Existenz ohne Horizont verliert die Form, die sie zu einem Leben macht.</p>',
			'<p>Endlichkeit ist nicht bloß ein Defekt. Sie ist eine Bedingung von Bedeutung. Weil Zeit begrenzt ist, haben Entscheidungen Gewicht. Weil Leben nicht unendlich verfügbar ist, wird Aufmerksamkeit kostbar. Weil Beziehungen sterblich sind, können sie tragisch, zärtlich und verbindlich sein. Weil wir verschwinden, ist es nicht egal, wie wir leben.</p>',
			'<p>Eine endlose Existenz wäre nicht automatisch tiefer. Sie könnte auch flacher werden. Wenn alles auf unendliche Verlängerung angelegt ist, verliert das Jetzt seine Dringlichkeit. Wenn der Tod nur noch als technisches Versagen gilt, wird das Leben selbst zur Warteschleife vor dem nächsten Update.</p>',
			'<p>Der Mensch braucht nicht die Verachtung seiner Grenzen. Er braucht ein würdiges Verhältnis zu ihnen, im Rahmen der <a href="/glossar/conditio-humana/">conditio humana</a>. Es gibt einen Unterschied zwischen dem Kampf gegen vermeidbares Leiden und dem Krieg gegen die Bedingung des Menschseins selbst. Heilung achtet das Leben. Die transhumanistische Erlösungsfantasie misstraut ihm.</p>',
		],
		'Kosmotechnik statt Götzendienst' => [
			'<p>Die richtige Antwort auf den Transhumanismus ist nicht Technikfeindlichkeit. Sie ist Entzauberung. Technik ist Werkzeug. Sie kann heilen, entlasten, verbinden, schützen, erweitern. Aber sie darf nicht zum Maßstab des Menschlichen werden. Sie darf nicht definieren, welches Leben als gelungen gilt, welche Körper wertvoll, welche Gefühle störend, welche Denkweisen ineffizient und welche Menschen verbesserungsbedürftig sind.</p>',
			'<p>Es braucht eine andere technologische Kultur. Der Philosoph Yuk Hui hat dafür den Begriff der <a href="/glossar/kosmotechnik/">Kosmotechnik</a> vorgeschlagen: die Einsicht, dass es nicht die eine, universale Technik gibt, sondern dass technische Entwicklung immer in eine kosmische und moralische Ordnung eingebettet ist — in Beziehung zu Körper, Kultur, Natur, Gemeinschaft, Ort und Grenze. Kosmotechnik bedeutet nicht Rückzug in Romantik. Sie bedeutet, technische Entwicklung nicht aus jeder Bindung zu lösen. Sie fragt nicht nur, was machbar ist. Sie fragt, in welche Ordnung das Machbare eingebettet wird.</p>',
			'<p>Eine humane technologische Kultur müsste deshalb anders beginnen. Nicht mit der Frage: Wie überwinden wir den Menschen? Sondern: Welche Technik dient dem Leben, ohne es zu entwürdigen? Nicht: Wie machen wir Menschen kompatibel mit Systemen? Sondern: Wie bauen wir Systeme, die menschliche Verletzlichkeit, Aufmerksamkeit, Körperlichkeit und Würde respektieren? Nicht: Wie verlängern wir das Leben einiger weniger ins Absurde? Sondern: Wie verbessern wir die Lebensbedingungen vieler im Konkreten? Nicht: Wie fliehen wir aus dem Körper? Sondern: Wie bewohnen wir ihn gerechter, gesünder und bewusster?</p>',
			'<p>Das wäre echter Fortschritt: nicht die Abschaffung des Menschen, sondern die Befreiung des Menschen von Systemen, die ihn schon heute deformieren.</p>',
		],
		'Der Gegenentwurf' => [
			'<p>Der Mensch ist kein defektes Gerät. Der Körper ist kein Gefängnis. Bewusstsein ist keine Datei. Sterblichkeit ist kein Softwarefehler.</p>',
			'<p>Wir müssen nicht kleiner von Technik denken. Wir müssen größer vom Menschen denken. Größer heißt nicht größenwahnsinnig. Es heißt: den Menschen nicht auf Leistung, Daten, Gene, Rechenprozesse oder Marktwert zu reduzieren. Es heißt, das Lebendige nicht zu verachten, nur weil es verletzlich ist. Es heißt, die Grenze nicht sofort als Feind zu behandeln. Es heißt, Fortschritt nicht daran zu messen, wie weit wir uns vom Menschlichen entfernen, sondern wie tief wir ihm gerecht werden.</p>',
			'<p>Der transhumanistische Traum vom ewigen Leben ist deshalb kein mutiger Blick nach vorn. Er ist oft eine Flucht vor der schwersten Aufgabe: dieses endliche Leben so zu gestalten, dass es nicht permanent nach Flucht verlangt.</p>',
			'<p>Die Zukunft des Menschen liegt nicht darin, sich selbst abzuschaffen. Sie liegt darin, Bedingungen zu schaffen, unter denen Menschsein nicht als Mangel erlebt werden muss. Das wäre die eigentliche Revolution.</p>',
		],
	];

	$sources = [
		'<strong>Altos Labs</strong> — 2022 mit 3 Mrd. USD Startfinanzierung gegründet (u. a. Jeff Bezos, Yuri Milner, ARCH Venture Partners); bestfinanzierter Biotech-Start der Geschichte; wissenschaftlicher Berater ist der Nobelpreisträger Shinya Yamanaka.',
		'<strong>Retro Biosciences</strong> — gegründet mit 180 Mio. USD Seed-Kapital von Sam Altman; Series-A-Runde mit angestrebter Bewertung von ca. 5 Mrd. USD ohne klinische Daten; erklärtes Ziel: zehn zusätzliche gesunde Lebensjahre. (Financial Times / STAT News, 2025)',
		'<strong>Methuselah Foundation</strong> — von Peter Thiel mit 1 Mio. USD unterstützt; Leitslogan „make 90 the new 50 by 2030".',
		'<strong>Raj Chetty et al.</strong> — „The Association Between Income and Life Expectancy in the United States, 2001–2014", <em>JAMA</em> 2016; 1,4 Mrd. Steuerdatensätze. Lebenserwartungslücke reichstes/ärmstes 1 %: 15 Jahre (Männer), 10 Jahre (Frauen).',
		'<strong>David Chalmers</strong> — „Facing Up to the Problem of Consciousness", 1995 (das „hard problem of consciousness").',
		'<strong>OpenWorm</strong> — seit 2011 laufendes Open-Science-Projekt zur vollständigen digitalen Emulation des Nervensystems von <em>C. elegans</em> (302 Neuronen).',
		'<strong>Derek Parfit</strong> — <em>Reasons and Persons</em>, 1984 (Teletransportations-Gedankenexperiment, Problem der personalen Identität).',
		'<strong>Nectome</strong> — Brain-Preservation-Startup; das MIT beendete die Kooperation 2018.',
		'<strong>Bernard Williams</strong> — „The Makropulos Case: Reflections on the Tedium of Immortality", in: <em>Problems of the Self</em>, 1973.',
		'<strong>Yuk Hui</strong> — <em>The Question Concerning Technology in China: An Essay in Cosmotechnics</em>, 2016.',
		'<strong>Erich Fromm</strong> — Unterscheidung Biophilie/Nekrophilie, u. a. in <em>The Anatomy of Human Destructiveness</em>, 1973.',
	];

	$separator = "<!-- wp:separator -->\n<hr class=\"wp-block-separator has-alpha-channel-opacity\"/>\n<!-- /wp:separator -->\n\n";

	$out  = "<!-- wp:paragraph -->\n<p><em>" . $lead . "</em></p>\n<!-- /wp:paragraph -->\n\n";
	$out .= $separator;

	foreach ( $intro as $p ) {
		$out .= "<!-- wp:paragraph -->\n{$p}\n<!-- /wp:paragraph -->\n\n";
	}

	foreach ( $sections as $heading => $paragraphs ) {
		$out .= "<!-- wp:heading -->\n<h2>" . esc_html( $heading ) . "</h2>\n<!-- /wp:heading -->\n\n";
		foreach ( $paragraphs as $p ) {
			$out .= "<!-- wp:paragraph -->\n{$p}\n<!-- /wp:paragraph -->\n\n";
		}
	}

	$out .= $separator;

	$list_items = '';
	foreach ( $sources as $item ) {
		$list_items .= "<li>{$item}</li>\n";
	}
	$out .= "<!-- wp:details -->\n";
	$out .= "<details class=\"wp-block-details\"><summary>Quellen und Anmerkungen</summary>\n";
	$out .= "<!-- wp:list -->\n<ul>\n{$list_items}</ul>\n<!-- /wp:list -->\n";
	$out .= "</details>\n<!-- /wp:details -->\n\n";

	return trim( $out );
}

/**
 * Seedet die sechs Glossar-Begriffe des Sterblichkeits-Essays.
 * Bestehende Slugs werden übersprungen — keine Überschreibung vorhandener Inhalte.
 */
function hp_seed_sterblichkeit_glossary(): void {
	$entries = [
		[
			'slug'  => 'transhumanismus',
			'title' => 'Transhumanismus',
			'kurz'  => 'Eine im Silicon Valley verwurzelte ideologische Bewegung, die die Überwindung der biologischen Grenzen des Menschen (Altern, Tod) durch Technologie anstrebt. Agiert als säkulare Ersatzreligion für eine hyperreiche Elite zur Betäubung von existenzieller Verlustangst.',
		],
		[
			'slug'  => 'reduktionismus-methodischer',
			'title' => 'Reduktionismus (methodischer)',
			'kurz'  => 'Der fundamentale Fehler der modernen westlichen Wissenschaft seit Descartes, das Lebendige und das Bewusstsein rein als Summe mechanischer, berechenbarer Einzelteile zu betrachten. Ignoriert die holistische Komplexität biologischer und kosmischer Systeme.',
		],
		[
			'slug'  => 'biophobie',
			'title' => 'Biophobie',
			'kurz'  => 'Die pathologische Angst vor der Unberechenbarkeit, Vergänglichkeit und Fleischlichkeit des organischen Lebens, die sich im transhumanistischen Drang äußert, alles Lebendige in sterile, kontrollierbare Datensätze (Silizium) zu pressen.',
		],
		[
			'slug'  => 'algorithmische-oeffentlichkeit',
			'title' => 'Algorithmische Öffentlichkeit',
			'kurz'  => 'Ein durch Plattform-Kapitalismus deformierter digitaler Raum, in dem Aufmerksamkeitsökonomie und automatisierte Erregungsschleifen die Fragmentierung des gesellschaftlichen Diskurses forcieren. Sie entzieht dem Individuum die freie Urteilskraft und ersetzt Verständigung durch algorithmisch belohntes Lagerdenken.',
		],
		[
			'slug'  => 'kosmotechnik',
			'title' => 'Kosmotechnik',
			'kurz'  => 'Ein von Yuk Hui geprägter Begriff. Der Gegenentwurf zur linearen, westlichen Zerstörungstechnologie. Er fordert, dass technische Entwicklungen wieder an die jeweilige Kultur, Natur und kosmische Ordnung zurückgebunden werden, statt das Universum als tote Rechenmasse auszubeuten.',
		],
		[
			'slug'  => 'conditio-humana',
			'title' => 'Conditio humana',
			'kurz'  => 'Die fundamentale, unveränderliche Ur-Bedingung der menschlichen Existenz. Sie definiert das Menschsein durch Verletzlichkeit, Körperlichkeit und Sterblichkeit – Grenzen, die nicht als Systemfehler, sondern als die zwingende Voraussetzung für Sinn, Verbindlichkeit und Tragik begriffen werden.',
		],
		[
			'slug'  => 'determinismus-mechanistischer',
			'title' => 'Determinismus (mechanistischer)',
			'kurz'  => 'Das im Barock geprägte Weltbild, das das Universum und den menschlichen Geist als lineares, berechenbares Uhrwerk betrachtet. Ignoriert die Erkenntnisse der modernen Quantenphysik über fundamentale Unschärfen.',
		],
		[
			'slug'  => 'mind-uploading',
			'title' => 'Mind Uploading',
			'kurz'  => 'Die hypothetische transhumanistische Technologie, bei der das menschliche Gehirn vollständig kartiert und das Bewusstsein als digitaler Datensatz rekonstruiert werden soll.',
		],
		[
			'slug'  => 'enhancement-technologien',
			'title' => 'Enhancement-Technologien',
			'kurz'  => 'Technologische Eingriffe, die nicht der Heilung dienen, sondern der künstlichen Erweiterung und Optimierung des Menschen über die biologischen Speziesgrenzen hinaus.',
		],
	];

	foreach ( $entries as $entry ) {
		$existing = hp_glossar_find_by_slug( $entry['slug'] );
		if ( $existing instanceof WP_Post ) {
			continue;
		}

		$body = "<!-- wp:paragraph -->\n<p>" . esc_html( $entry['kurz'] ) . "</p>\n<!-- /wp:paragraph -->";

		$post_id = wp_insert_post( [
			'post_type'    => 'glossar',
			'post_status'  => 'publish',
			'post_name'    => $entry['slug'],
			'post_title'   => $entry['title'],
			'post_excerpt' => $entry['kurz'],
			'post_content' => $body,
		], true );

		if ( is_wp_error( $post_id ) || ! $post_id ) {
			continue;
		}

		update_post_meta( $post_id, '_hp_glossar_kurz', $entry['kurz'] );
	}
}
