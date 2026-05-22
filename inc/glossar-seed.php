<?php
/**
 * Glossar- & Essay-Seed — Hasimuener Journal
 *
 * Idempotentes Anlegen des Essays „Abrechnung mit dem Transhumanismus"
 * sowie der zugehörigen neun Glossar-Begriffe. Prüft pro Slug, ob ein
 * Eintrag bereits existiert; bestehende Inhalte werden nicht überschrieben.
 *
 * Auslöser: einmaliger Lauf im Admin-Kontext, gesteuert über einen
 * Versions-Flag (`hp_glossar_seed_version`). Nach erfolgtem Lauf kann
 * der require-Eintrag in functions.php entfernt werden.
 *
 * @package Hasimuener_Journal
 * @since   7.0.0
 */

defined( 'ABSPATH' ) || exit;

const HP_GLOSSAR_SEED_VERSION = '2026-05-22-perspektive';

function hp_run_glossar_seed_once(): void {
	if ( ! is_admin() ) {
		return;
	}

	if ( get_option( 'hp_glossar_seed_version' ) === HP_GLOSSAR_SEED_VERSION ) {
		return;
	}

	hp_seed_perspektive_essay();
	hp_seed_perspektive_glossary();

	update_option( 'hp_glossar_seed_version', HP_GLOSSAR_SEED_VERSION, false );
}
add_action( 'admin_init', 'hp_run_glossar_seed_once', 25 );

/**
 * Legt den Essay „Abrechnung mit dem Transhumanismus: Geist vs. Excel-Tabelle"
 * an, sofern nicht bereits per Slug vorhanden.
 */
function hp_seed_perspektive_essay(): void {
	$slug = 'abrechnung-transhumanismus';

	$existing = get_page_by_path( $slug, OBJECT, 'essay' );
	if ( $existing instanceof WP_Post ) {
		return;
	}

	$content = hp_get_perspektive_essay_content();

	$post_id = wp_insert_post( [
		'post_type'    => 'essay',
		'post_status'  => 'publish',
		'post_name'    => $slug,
		'post_title'   => 'Abrechnung mit dem Transhumanismus: Geist vs. Excel-Tabelle',
		'post_excerpt' => 'Eine radikale Abrechnung mit dem Transhumanismus und der Tech-Elite. Warum die Reduktion von Bewusstsein auf Datenverarbeitung ein fataler Irrtum ist.',
		'post_content' => $content,
	], true );

	if ( is_wp_error( $post_id ) || ! $post_id ) {
		return;
	}

	update_post_meta(
		$post_id,
		'_hp_meta_description',
		'Eine radikale Abrechnung mit dem Transhumanismus und der Tech-Elite. Warum die Reduktion von Bewusstsein auf Datenverarbeitung ein fataler Irrtum ist.'
	);

	update_post_meta( $post_id, '_hp_reading_minutes', 20 );

	if ( taxonomy_exists( 'topic' ) ) {
		wp_set_object_terms( $post_id, [ 'Gesellschaft', 'Technologie', 'Philosophie' ], 'topic', false );
	}
}

/**
 * Liefert den vollständigen HTML-Body des Essays als Gutenberg-Blockfolge.
 */
function hp_get_perspektive_essay_content(): string {
	$blocks = [
		'<p>Der <strong>Transhumanismus</strong> tritt selten als Religion auf. Er spricht lieber die Sprache der Forschung, der Innovation, der Effizienz und des technischen Fortschritts. Er verspricht Heilung, längeres Leben, höhere Intelligenz, bessere Körper, perfektere Entscheidungen, vielleicht sogar eines Tages die Überwindung des Todes. Wer wollte dagegen sein? Wer wollte Krankheit, Demenz, körperlichen Verfall oder frühes Sterben verteidigen?</p>',
		'<p>Genau hier beginnt die Verführung.</p>',
		'<p>Denn der stärkste Teil des transhumanistischen Versprechens ist nicht falsch. Es ist gut, Leiden zu lindern. Es ist gut, Krankheiten zu behandeln. Es ist gut, Prothesen zu verbessern, blinden Menschen Sehen zu ermöglichen, gelähmten Menschen Bewegung zurückzugeben, Alterungsprozesse besser zu verstehen und medizinische Möglichkeiten zu erweitern. Eine Kritik dieser Bewegung darf deshalb nicht mit einer blinden Fortschrittsfeindschaft verwechselt werden. Nicht jede Form technologischer Erweiterung ist Entmenschlichung. Nicht jede Forschung an Langlebigkeit ist Hybris. Nicht jeder medizinische Eingriff ist ein Angriff auf das Wesen des Menschen.</p>',
		'<p>Der Bruch beginnt an einer anderen Stelle.</p>',
		'<p>Er beginnt dort, wo Heilung zur Erlösungsfantasie wird. Dort, wo der Mensch nicht mehr als lebendiges, verletzliches, soziales Wesen verstanden wird, sondern als defektes System. Als biologischer Altbau. Als schlecht optimierte Maschine. Als Bündel von Daten, Reizen und Rechenprozessen. Dort, wo der Körper nicht mehr Heimat ist, sondern Hindernis. Dort, wo Sterblichkeit nicht mehr zur menschlichen Existenz gehört, sondern als technisches Problem behandelt wird, das nur noch auf seine Lösung wartet.</p>',
		'<p>Technologie wird gefährlich, sobald sie aus der Medizin eine Metaphysik macht.</p>',
	];

	$sections = [
		'Der Mensch als fehlerhafte Maschine' => [
			'<p>Das eigentliche Problem liegt nicht in einzelnen Werkzeugen. Es liegt im Menschenbild, das sie begleitet. Dieses Menschenbild basiert auf einem radikalen, <strong>methodischen Reduktionismus</strong>. Es betrachtet Bewusstsein als Datenverarbeitung, den Körper als Hardware, Erinnerung als Speicher, Intelligenz als reine Rechenleistung, Identität als Muster und das Leben als mathematisches Optimierungsproblem. Was sich nicht messen, modellieren, beschleunigen oder kontrollieren lässt, erscheint darin zweitrangig, irrational oder veraltet.</p>',
			'<p>Aber der Mensch ist keine Excel-Tabelle mit Stoffwechsel.</p>',
			'<p>Die Ironie dabei ist: Die Tech-Elite gibt sich avantgardistisch, operiert aber mit einem Weltbild, das wissenschaftsgeschichtlich im mechanistischen Determinismus von Gottfried Wilhelm Leibniz hängengeblieben ist. Sie betrachten das Universum und den Geist als ein lineares, berechenbares Uhrwerk. Dabei ignorieren sie geflissentlich, dass Max Planck und Werner Heisenberg diese mechanische Kausalität vor über einhundert Jahren zertrümmert haben. Die Quantenphysik hat gezeigt, dass die fundamentale Realität eben nicht deterministisch, sondern voller Unschärfen, Brüche und nicht-linearer Potenziale ist. Während antike Denker wie Heraklit bereits das universelle Prinzip des permanenten, dynamischen Werdens verstanden, klammert sich der Transhumanismus an ein vor-modernes Konzept starrer, berechenbarer Materie. Ihr Verständnis von Intelligenz ist kein Blick in die Zukunft, sondern der leblose Rückzug in ein überholtes, mechanisches Weltbild des Barock.</p>',
			'<p>Ein Mensch ist nicht bloß eine Ansammlung von Funktionen. Er ist nicht nur sein Gehirn, nicht nur sein Genom, nicht nur seine Produktivität. Menschliches Leben entsteht aus Körper, Sprache, Erinnerung, Beziehung, Schmerz, Geschichte, Endlichkeit und Sinn. Es ist nicht einfach Information, die zufällig auf biologischem Trägermaterial läuft.</p>',
			'<p>Genau diese Verwechslung ist zentral: Das Lebendige wird behandelt, als sei es im Kern bereits maschinell. Die Maschine erscheint dann nicht mehr als Werkzeug des Menschen, sondern als dessen bessere Version. Das Organische wird zum Provisorium erklärt. Das Digitale wird zur Verheißung.</p>',
			'<p>Dabei ist eine Maschine immer nur in Teilbereichen überlegen. Sie kann schneller rechnen, präziser sortieren, größere Datenmengen verarbeiten. Aber aus funktionaler Überlegenheit folgt kein existenzieller Vorrang. Ein Taschenrechner ist besser im Rechnen als ein Kind. Trotzdem ist das Kind nicht die minderwertige Version des Taschenrechners. Der Mensch ist nicht deshalb wertvoll, weil er effizient ist. Er ist wertvoll, bevor er überhaupt etwas leistet.</p>',
		],
		'Die Ersatzreligion der Kontrolle' => [
			'<p>Der moderne Technokratismus gibt sich nüchtern, aber sein innerer Antrieb ist oft religiöser, als seine Anhänger zugeben würden. Er verspricht das, was Religionen immer versprochen haben: Erlösung vom Leiden, Überwindung der Begrenzung, Rettung vor dem Tod, Fortexistenz über den Zerfall des Körpers hinaus. Dahinter verbirgt sich eine tiefe <strong>Biophobie</strong> – die pathologische Angst vor der Unberechenbarkeit, der Fleischlichkeit und der Vergänglichkeit des echten, organischen Lebens. Alles Lebendige soll in sterile, kontrollierbare Datensätze gepresst werden, um der Ohnmacht der eigenen Sterblichkeit zu entkommen.</p>',
			'<p>Die alten Symbole wurden dabei nur ersetzt. Aus der Seele wird Information. Aus Auferstehung wird Upload. Aus dem Paradies wird die Simulation. Aus Askese wird Selbstoptimierung. Aus Gott wird die Technik. Aus Erlösung wird Produktentwicklung.</p>',
			'<p>Das macht das System nicht automatisch funktionsunfähig, aber es macht es unehrlich, wenn es so tut, als sei es bloß Wissenschaft. Wissenschaft beschreibt, prüft, verwirft, korrigiert. Ideologie verspricht eine Richtung der Geschichte. Der Traum kippt genau dort in Ideologie, wo er nicht mehr fragt, was Technik kann, sondern behauptet, wohin der Mensch sich entwickeln müsse.</p>',
			'<p>Darin liegt sein autoritärer Kern. Wer den Menschen als Mängelwesen definiert, braucht irgendwann Instanzen, die festlegen, welche Mängel beseitigt werden sollen. Wer Optimierung zum Ziel erklärt, muss bestimmen, was als besser gilt. Solche Fragen sind nie rein technisch. Sie sind politisch, ethisch und sozial. Und sie sind gefährlich, wenn sie von denen beantwortet werden, die ohnehin schon über Kapital, Infrastruktur und Deutungsmacht verfügen.</p>',
		],
		'Die Klassenfrage der Optimierung' => [
			'<p>Die technologische Zukunft wird gerne als kollektives Menschheitsprojekt verkauft. Aber technologische Revolutionen kommen selten gleichmäßig bei der Menschheit an. Sie beginnen dort, wo Geld, Labore, Plattformen, Patente und Zugang konzentriert sind.</p>',
			'<p>Deshalb muss man die Machtfrage stellen: Wer wird optimiert? Wer bleibt zurück? Wer besitzt die Infrastruktur? Wer kontrolliert die Daten? Wer bestimmt die Norm?</p>',
			'<p>Eine Gesellschaft, die schon heute extreme Ungleichheit produziert, wird durch Enhancement-Technologien nicht automatisch gerechter. Wenn biologische oder digitale Erweiterungen marktförmig organisiert werden, entsteht eine neue Klassengesellschaft. Oben diejenigen, die sich Zugriff auf Optimierung kaufen können. Unten diejenigen, deren Körper weiterhin verschleißen, deren Daten geerntet werden und deren Lebensbedingungen sich nicht verbessern.</p>',
			'<p>Der Überbau spricht vom Menschen der Zukunft, während die Gegenwart zerfällt. Er träumt von digitaler Unsterblichkeit, während Bildungssysteme ausbluten, Pflegekräfte kollabieren und Demokratien durch die Logiken einer <strong>algorithmischen Öffentlichkeit</strong> zerrieben werden. Diese manipulativen, automatisierten Erregungsschleifen sorgen gezielt dafür, dass der Unterbau in ständiger Ablenkung und Fragmentierung gehalten wird, statt gegen die Entstehung dieser neuen technokratischen Klassengesellschaft aufzubegehren.</p>',
			'<p>Die Flucht in eine fantastische Zukunft entlastet von der Reparatur der konkreten Gegenwart. Wer vom Upload des Bewusstseins träumt, muss sich weniger mit der Einsamkeit alter Menschen beschäftigen. Wer vom optimierten Körper schwärmt, muss weniger über Arbeitsbedingungen sprechen, die Körper zerstören. Die große Obszönität liegt darin, dass man das Falsche zuerst will.</p>',
		],
		'Digitale Unsterblichkeit ist keine Unsterblichkeit' => [
			'<p>Besonders deutlich wird der Denkfehler beim Traum vom <em>Mind Uploading</em>. Die Idee klingt spektakulär: Das Gehirn wird kartiert, das Bewusstsein rekonstruiert, die Persönlichkeit digitalisiert, der Mensch lebt als Informationsmuster weiter.</p>',
			'<p>Aber selbst wenn man eines Tages eine perfekte digitale Kopie eines Gehirns erzeugen könnte, wäre damit die entscheidende Frage nicht gelöst: Warum sollte diese Kopie <em>ich</em> sein?</p>',
			'<p>Eine Kopie kann sprechen wie ich, erinnern wie ich, reagieren wie ich, meine biografischen Muster fortsetzen. Aber Ähnlichkeit ist keine Kontinuität. Simulation ist keine Erfahrung. Ein digitales Modell meiner Person wäre vielleicht ein beeindruckendes Archiv, vielleicht ein interaktives Denkmal, vielleicht eine perfekte Täuschung für andere. Aber es wäre nicht die Fortsetzung meines gelebten Bewusstseins.</p>',
			'<p>Der Tod wird dadurch nicht überwunden. Er wird nur ästhetisch kaschiert. Digitale Unsterblichkeit ist keine Auferstehung, sondern Nachlassverwaltung mit Benutzeroberfläche. Der Wunsch, nicht zu verschwinden, ist menschlich. Aber genau deshalb ist es gefährlich, diese Angst in ein Geschäftsmodell zu verwandeln. Wer Menschen digitale Fortexistenz verkauft, verkauft Trost. Und Trost ist einer der empfindlichsten Märkte überhaupt.</p>',
		],
		'Endlichkeit als Bedingung von Sinn' => [
			'<p>Der Transhumanismus behandelt Sterblichkeit als bloße Niederlage. Aber vielleicht ist gerade das sein tiefster Irrtum. Endlichkeit ist nicht bloß ein Defekt. Sie ist eine Bedingung von Bedeutung. Weil Zeit begrenzt ist, haben Entscheidungen Gewicht. Weil Leben nicht unendlich verfügbar ist, wird Aufmerksamkeit kostbar. Weil Beziehungen sterblich sind, können sie tragisch, zärtlich und verbindlich sein. Weil wir verschwinden, ist es nicht egal, wie wir leben.</p>',
			'<p>Eine endlose Existenz wäre nicht automatisch tiefer. Sie könnte flacher werden. Wenn alles auf unendliche Verlängerung angelegt ist, verliert das Jetzt seine Dringlichkeit. Wenn der Tod nur noch als technisches Versagen gilt, wird das Leben selbst zur Warteschleife vor dem nächsten Update.</p>',
			'<p>Der Mensch braucht nicht die Verachtung seiner Grenzen. Er braucht ein würdiges Verhältnis zu ihnen. Das heißt nicht, Leid zu romantisieren. Medizinischer Fortschritt bleibt notwendig. Aber es gibt einen fundamentalen Unterschied zwischen dem Kampf gegen vermeidbares Leiden und dem Krieg gegen die <em>conditio humana</em>. Heilung achtet das Leben. Die transhumanistische Erlösungsfantasie misstraut ihm.</p>',
		],
		'Der Gegenentwurf: Kosmotechnik statt Götzendienst' => [
			'<p>Die richtige Antwort auf diesen Größenwahn ist nicht blinde Technikfeindlichkeit. Sie ist Entzauberung.</p>',
			'<p>Wir müssen hocheffiziente Werkzeuge entwickeln, ohne sie anzubeten. Wir brauchen eine neue <strong>Kosmotechnik</strong> – ein Denken, das technische Entwicklungen nicht als lineare Zerstörungs- und Optimierungsmaschinen begreift, die das Universum als tote Rechenmasse ausbeuten. Technik muss wieder an die organische Ordnung, an die Kultur, an die Natur und an die menschliche Verletzlichkeit zurückgebunden werden.</p>',
			'<p>Eine humane technologische Kultur müsste anders beginnen. Nicht mit der Frage: Wie überwinden wir den Menschen? Sondern: Welche Technik dient dem Leben, ohne es zu entwürdigen?</p>',
			'<p>Nicht: Wie machen wir Menschen kompatibel mit Systemen?<br>Sondern: Wie bauen wir Systeme, die menschliche Aufmerksamkeit, Körperlichkeit, Würde und Gemeinschaft respektieren?</p>',
			'<p>Der Mensch ist kein defektes Gerät. Der Körper ist kein Gefängnis. Bewusstsein ist keine Datei. Sterblichkeit ist kein Softwarefehler. Wir müssen nicht kleiner von Technik denken. Wir müssen größer vom Menschen denken.</p>',
			'<p>Größer heißt nicht größenwahnsinnig. Es heißt, das Lebendige nicht zu verachten, nur weil es verletzlich ist. Es heißt, Fortschritt nicht daran zu messen, wie weit wir uns vom Menschlichen entfernen, sondern wie tief wir ihm gerecht werden. Die Zukunft des Menschen liegt nicht darin, sich selbst abzuschaffen. Sie liegt darin, endlich Bedingungen zu schaffen, unter denen das endliche Menschsein nicht mehr als Mangel erlebt werden muss.</p>',
		],
	];

	$out = '';
	foreach ( $blocks as $p ) {
		$out .= "<!-- wp:paragraph -->\n{$p}\n<!-- /wp:paragraph -->\n\n";
	}
	foreach ( $sections as $heading => $paragraphs ) {
		$out .= "<!-- wp:heading -->\n<h2>" . esc_html( $heading ) . "</h2>\n<!-- /wp:heading -->\n\n";
		foreach ( $paragraphs as $p ) {
			$out .= "<!-- wp:paragraph -->\n{$p}\n<!-- /wp:paragraph -->\n\n";
		}
	}

	return trim( $out );
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
		$existing = get_page_by_path( $entry['slug'], OBJECT, 'glossar' );
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
