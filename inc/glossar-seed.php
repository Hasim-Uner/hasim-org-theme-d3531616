<?php
/**
 * Glossar- & Essay-Seed — Hasimuener Journal
 *
 * Idempotentes Anlegen des Essays „Sterblichkeit ist kein Softwarefehler"
 * sowie der zugehörigen Glossar-Begriffe. Alle Begriffe sind hier zentral
 * und absolut redundanzfrei in einer einzigen Funktion definiert.
 *
 * Auslöser: einmaliger Lauf pro `HP_GLOSSAR_SEED_VERSION` im
 * Admin-Kontext, gesteuert über die Option `hp_glossar_seed_version`.
 *
 * @package Hasimuener_Journal
 * @since   7.0.0
 */

defined( 'ABSPATH' ) || exit;

const HP_GLOSSAR_SEED_VERSION = '2026-05-24-sterblichkeit-r6-text';

function hp_run_glossar_seed_once(): void {
	if ( ! is_admin() ) {
		return;
	}

	if ( get_option( 'hp_glossar_seed_version' ) === HP_GLOSSAR_SEED_VERSION ) {
		return;
	}

	hp_remove_abrechnung_transhumanismus_essay();
	hp_seed_all_glossary_terms();
	hp_seed_sterblichkeit_essay();

	update_option( 'hp_glossar_seed_version', HP_GLOSSAR_SEED_VERSION, false );
}
add_action( 'admin_init', 'hp_run_glossar_seed_once', 25 );

/**
 * Einmaliger Cleanup: löscht den abgelösten Essay „Abrechnung mit dem
 * Transhumanismus" (Slug: abrechnung-transhumanismus) endgültig aus der
 * Datenbank.
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
 * Seedet alle 18 einzigartigen Glossar-Begriffe absolut redundanzfrei.
 */
function hp_seed_all_glossary_terms(): void {
	$entries = [
		// --- Perspektive- & System-Komplex ---
		[
			'slug'     => 'transhumanismus',
			'title'    => 'Transhumanismus',
			'kurz'     => 'Eine im Silicon Valley verwurzelte ideologische Bewegung, die die Überwindung der biologischen Grenzen des Menschen (Altern, Tod) durch Technologie anstrebt. Agiert als säkulare Ersatzreligion für eine hyperreiche Elite zur Betäubung von existenzieller Verlustangst.',
			'synonyme' => [ 'transhumanistische', 'transhumanistischen', 'transhumanistischer', 'transhumanistischem', 'transhumanistisches' ],
		],
		[
			'slug'     => 'reduktionismus-methodischer',
			'title'    => 'Reduktionismus (methodischer)',
			'kurz'     => 'Der fundamentale Fehler der modernen westlichen Wissenschaft seit Descartes, das Lebendige und das Bewusstsein rein als Summe mechanischer, berechenbarer Einzelteile zu betrachten. Ignoriert die holistische Komplexität biologischer und kosmischer Systeme.',
			'synonyme' => [ 'methodischer Reduktionismus', 'methodischen Reduktionismus', 'cartesianischer Dualismus', 'cartesianischen Dualismus' ],
		],
		[
			'slug'     => 'biophobie',
			'title'    => 'Biophobie',
			'kurz'     => 'Die pathologische Angst vor der Unberechenbarkeit, Vergänglichkeit und Fleischlichkeit des organischen Lebens, die sich im transhumanistischen Drang äußert, alles Lebendige in sterile, kontrollierbare Datensätze (Silizium) zu pressen.',
			'synonyme' => [ 'transhumanistische Biophobie', 'transhumanistischen Biophobie' ],
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

		// --- Sterblichkeits- & Erkenntnistheorie-Komplex ---
		[
			'slug'     => 'conditio-humana',
			'title'    => 'Conditio humana',
			'kurz'     => 'Die fundamentale, unveränderliche Ur-Bedingung der menschlichen Existenz. Sie definiert das Menschsein durch Verletzlichkeit, Körperlichkeit und Sterblichkeit – Grenzen, die nicht als Systemfehler, sondern als die zwingende Voraussetzung für Sinn, Verbindlichkeit und Tragik begriffen werden.',
			'synonyme' => [ 'conditio humana' ],
		],
		[
			'slug'     => 'determinismus-mechanistischer',
			'title'    => 'Determinismus (mechanistischer)',
			'kurz'     => 'Das im Barock geprägte Weltbild, das das Universum und den menschlichen Geist als lineares, berechenbares Uhrwerk betrachtet. Ignoriert die Erkenntnisse der modernen Quantenphysik über fundamentale Unschärfen.',
			'synonyme' => [ 'mechanistischer Determinismus', 'mechanistischen Determinismus', 'berechenbares Uhrwerk' ],
		],
		[
			'slug'     => 'mind-uploading',
			'title'    => 'Mind Uploading',
			'kurz'     => 'Die hypothetische transhumanistische Technologie, bei der das menschliche Gehirn vollständig kartiert und das Bewusstsein als digitaler Datensatz rekonstruiert werden soll.',
			'synonyme' => [ 'Mind-Uploading', 'Upload des Menschen', 'digitale Unsterblichkeit' ],
		],
		[
			'slug'     => 'enhancement-technologien',
			'title'    => 'Enhancement-Technologien',
			'kurz'     => 'Technologische Eingriffe, die nicht der Heilung dienen, sondern der künstlichen Erweiterung und Optimierung des Menschen über die biologischen Speziesgrenzen hinaus.',
			'synonyme' => [ 'Enhancement', 'Enhancements', 'technologische Erweiterungen', 'kognitive Erweiterungen' ],
		],
		[
			'slug'     => 'phanomenologie-des-leibes',
			'title'    => 'Phänomenologie des Leibes',
			'kurz'     => 'Der philosophische Gegenentwurf (u. a. Merleau-Ponty) zum cartesianischen Dualismus. Beschreibt, dass der Mensch seinen Körper nicht bloß besitzt, sondern als leibliches Wesen in ständiger, unteilbarer Rückkopplung mit der Umwelt existiert. Bewusstsein ist primär ein leiblicher Vollzug.',
			'synonyme' => [ 'Phänomenologie des Leibs', 'leiblicher Vollzug' ],
		],
		[
			'slug'     => 'verkoerperte-kognition',
			'title'    => 'Verkörperte Kognition (Embodied Cognition)',
			'kurz'     => 'Der kognitionswissenschaftliche Nachweis, dass Denken und Bewusstsein keine abstrakten, gehirn-isolierten Rechenprozesse sind, sondern untrennbar an die physischen Schleifen, das Nervensystem und die gesamte Biologie des lebendigen Körpers gebunden bleiben.',
			'synonyme' => [ 'verkörperte Kognition', 'verkörperten Kognition', 'Embodied Cognition' ],
		],
		[
			'slug'     => 'hartes-problem-des-bewusstseins',
			'title'    => 'Hartes Problem des Bewusstseins',
			'kurz'     => 'Die von David Chalmers benannte fundamentale Erklärungslücke der Wissenschaft, warum physikalische oder informationsverarbeitende Prozesse im Gehirn überhaupt von subjektivem Erleben (Qualia) begleitet werden. Vom Transhumanismus dogmatisch ignoriert.',
			'synonyme' => [ 'harte Problem des Bewusstseins', 'hartes Problem', 'harte Problem', 'hard problem of consciousness' ],
		],
		[
			'slug'  => 'biophilie',
			'title' => 'Biophilie',
			'kurz'  => 'Die von Erich Fromm definierte tiefe psychologische Zuneigung zu allem Lebendigen, Wachsenden und fundamental Unberechenbaren. Bildet den direkten evolutionären Gegenpol zur transhumanistischen Biophobie.',
		],
		[
			'slug'     => 'pessimismus-philosophischer',
			'title'    => 'Pessimismus (philosophischer)',
			'kurz'     => 'Die philosophische Haltung (historisch geprägt durch Arthur Schopenhauer), die das Leiden als Grundton des Daseins begreift. Im Essay demaskiert als Fehlschluss, der historisch-ökonomisch erzeugtes Leid mit einer metaphysischen Signatur des Menschseins verwechselt.',
			'synonyme' => [ 'Pessimismus', 'philosophischer Pessimismus' ],
		],
	];

	$changed = false;

	foreach ( $entries as $entry ) {
		$existing = get_page_by_path( $entry['slug'], OBJECT, 'glossar' );
		if ( $existing instanceof WP_Post ) {
			$changed = hp_update_seeded_glossar_meta( $existing->ID, $entry ) || $changed;
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

		$changed = hp_update_seeded_glossar_meta( (int) $post_id, $entry ) || $changed;
	}

	if ( $changed ) {
		hp_glossar_seed_bump_cache_version();
	}
}

/**
 * Aktualisiert Seed-Meta für bestehende und neue Glossar-Begriffe.
 *
 * @param int                  $post_id Glossar-Post-ID.
 * @param array<string, mixed> $entry   Seed-Definition.
 * @return bool Ob sich ein Wert geändert hat.
 */
function hp_update_seeded_glossar_meta( int $post_id, array $entry ): bool {
	$changed = false;

	if ( isset( $entry['kurz'] ) ) {
		$changed = update_post_meta( $post_id, '_hp_glossar_kurz', (string) $entry['kurz'] ) || $changed;
	}

	if ( ! empty( $entry['synonyme'] ) && is_array( $entry['synonyme'] ) ) {
		$existing = (string) get_post_meta( $post_id, '_hp_glossar_synonyme', true );
		$merged   = hp_merge_glossar_synonyms( $existing, $entry['synonyme'] );

		$changed = update_post_meta( $post_id, '_hp_glossar_synonyme', implode( ', ', $merged ) ) || $changed;
	}

	return $changed;
}

/**
 * Merged bestehende und neue Synonyme case-insensitiv und stabil.
 *
 * @param string       $existing_csv Bestehende Synonyme.
 * @param array<mixed> $seeded       Neue Seed-Synonyme.
 * @return array<int, string>
 */
function hp_merge_glossar_synonyms( string $existing_csv, array $seeded ): array {
	$items = array_merge(
		array_map( 'trim', explode( ',', $existing_csv ) ),
		array_map( 'strval', $seeded )
	);

	$seen   = [];
	$merged = [];

	foreach ( $items as $item ) {
		$item = trim( (string) $item );
		if ( '' === $item ) {
			continue;
		}

		$key = function_exists( 'mb_strtolower' ) ? mb_strtolower( $item, 'UTF-8' ) : strtolower( $item );
		if ( isset( $seen[ $key ] ) ) {
			continue;
		}

		$seen[ $key ] = true;
		$merged[]     = $item;
	}

	return $merged;
}

/**
 * Invalidiert Auto-Link-Caches nach Seed-Meta-Updates.
 */
function hp_glossar_seed_bump_cache_version(): void {
	$new_version = (int) get_option( 'hp_glossar_version', 0 ) + 1;
	update_option( 'hp_glossar_version', $new_version, false );

	global $wpdb;
	$wpdb->query(
		"DELETE FROM {$wpdb->options}
		 WHERE option_name LIKE '_transient_hp_gl_%'
		    OR option_name LIKE '_transient_timeout_hp_gl_%'"
	);
}

/**
 * Legt den Essay „Sterblichkeit ist kein Softwarefehler" idempotent an.
 */
function hp_seed_sterblichkeit_essay(): void {
	$slug            = 'sterblichkeit-kein-softwarefehler';
	$content_version = 'r6-werden-update';
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
 * als Gutenberg-Blockfolge mit internen Verlinkungen sowie einem abschließenden Quellenapparat.
 */
function hp_get_sterblichkeit_essay_content(): string {
	$lead = 'Milliarden fließen in die Abschaffung des Todes. Das ist kein Fortschritt, sondern eine Flucht – und der Mensch wird nicht gerettet, indem man ihn abschafft.';

	$sections = [
		'I. Die Konfrontation mit dem Fleisch' => [
			'<p>Ein kranker Körper riecht nicht nach Silizium. Er riecht nach Desinfektionsmittel, nach Schweiß, nach der schweren Biologie eines Organismus, der sich nicht mehr im Griff hat. Ich habe das am Krankenhausbett meines krebskranken Onkels gesehen. Vor dieser Hinfälligkeit kann man die Augen verschließen; wegerklären lässt sie sich nicht. Wir sind Fleisch, gebunden an die Chronologie des Alterns.</p>',
			'<p>Im Silicon Valley wird dieses Faktum anders verhandelt. Dort gilt Altern nicht als Bedingung der Existenz, sondern als Konstruktionsfehler — als „Akkumulation zellulärer Schäden", behebbar wie fehlerhafter Code. Das ist kein Zerrbild, sondern erklärtes Programm, unterlegt mit enormem Kapital: Altos Labs startete 2022 mit drei Milliarden Dollar, gestützt von Jeff Bezos — der bestfinanzierte Biotech-Start der Geschichte. Retro Biosciences, finanziert vom OpenAI-Chef Sam Altman, jagt eine Fünf-Milliarden-Bewertung mit dem Ziel, dem Leben zehn gesunde Jahre hinzuzufügen. Ray Kurzweil terminiert das Verschmelzen von Mensch und Maschine auf das Jahr 2045.</p>',
			'<p>Bevor man das verwirft, muss man den stärksten Einwand der Gegenseite gelten lassen: Altern ist die größte Einzelursache menschlichen Leidens; täglich sterben weltweit über hunderttausend Menschen an altersbedingten Ursachen. Wenn sich dieser Prozess medizinisch verlangsamen lässt — wäre es nicht zynisch, es nicht zu versuchen? Das Argument ist ernst zu nehmen. Es ist gut, Leiden zu lindern, Krankheiten zu behandeln, verlorene Funktionen wiederherzustellen. Eine Kritik, die das bestreitet, wäre wohlfeile Fortschrittsfeindschaft.</p>',
			'<p>Der Bruch liegt nicht hier. Er liegt dort, wo aus Medizin eine Metaphysik wird — wo Heilung nicht mehr heilen will, sondern erlösen. Wo der Mensch nicht mehr als verletzliches, sterbliches Wesen gilt, sondern als defektes System, das auf sein Update wartet. Der Transhumanismus wird nicht durch seine Werkzeuge fragwürdig, sondern durch das Menschenbild, das er mitliefert.</p>',
		],
		'II. Der Kategorienfehler: Wenn der Geist das Fleisch verlässt' => [
			'<p>Der transhumanistische Traum vom „Mind Uploading" — der Übertragung des Bewusstseins auf einen Datenträger — lebt von einem alten philosophischen Irrtum. René Descartes spaltete die Welt im 17. Jahrhundert in eine denkende Substanz und eine ausgedehnte, materielle Welt. Der Körper wurde zur Maschine, gesteuert von einem unkörperlichen Geist. Das Silicon Valley hat diesen Dualismus lediglich digitalisiert: Das Gehirn gilt als Prozessor, der Körper als austauschbare Peripherie, das Selbst als Information, die zufällig auf biologischer Hardware läuft.</p>',
			'<p>Gegen dieses Bild steht ein gut belegter Einwand. Die Phänomenologie des Leibes, wie sie Maurice Merleau-Ponty entwickelt hat, zeigt: Der Mensch <em>hat</em> seinen Körper nicht, er <em>ist</em> sein Körper. Wahrnehmung, Absicht, Orientierung in der Welt sind keine Rechenleistung, die im Schädel stattfindet — sie sind leiblich. Ich bin kein Pilot in einem Fahrzeug. Das Selbst entsteht im Vollzug: in der Bewegung, im Tasten, im Stoffwechsel, im Zusammenspiel von Hormonen, im Mikrobiom des Darms, in der ständigen, größtenteils unbewussten Rückkopplung zwischen Körper und Umwelt. Die kognitionswissenschaftliche Forschung zur „verkörperten Kognition" hat diese Einsicht in den letzten Jahrzehnten breit untermauert: Denken ist kein körperloser Prozess.</p>',
			'<p>Und dieser Körper ist kein dummes Trägermaterial. Er ist, wenn man ihn ernst nimmt, ein erstaunliches Ding. Schneide dir in den Finger — und ohne dass du etwas tust, ohne dass du auch nur weißt, wie es geht, beginnt ein präzise gestufter Vorgang: Das Blut gerinnt, Zellen wandern ein, Gewebe wird neu gebildet, die Wunde schließt sich. Keine von Menschen gebaute Maschine kann das auch nur annähernd. Der Körper tut es ständig, in jeder Sekunde, an unzähligen Stellen zugleich.</p>',
			'<p>Er ist eine Welt für sich — viele Welten eigentlich, jede mit ihrer eigenen Logik und doch ineinander verschaltet. Das Immunsystem unterscheidet unzählige Bedrohungen, lernt aus ihnen, erinnert sich über Jahrzehnte. Der Darm beherbergt ein Mikrobiom aus Billionen Mikroorganismen, das Stoffwechsel, Immunabwehr und über die Darm-Hirn-Achse sogar die Stimmung mitprägt. Hormone, Nerven, Organe und Zellen stehen in einer ununterbrochenen Rückkopplung, die kein Ingenieur entworfen hat und niemand vollständig versteht. Und das Ganze passt sich an: Muskeln werden unter Belastung stärker, das Gehirn verschaltet sich neu, der Organismus stellt sich auf Höhe, Hitze, Anstrengung ein. Fällt ein Teil aus, gerät das Ganze ins Wanken. Der Körper ist in diesem Sinne intelligent — nicht weil er rechnet, sondern weil er sich organisiert, heilt und im Gleichgewicht hält. Wer das einmal wirklich sieht, dem erscheint die Rede vom „biologischen Altbau" nicht nur falsch, sondern seltsam blind.</p>',
			'<p>Es wäre nun verlockend, dem transhumanistischen Bild vom Körper als Schrott einfach das Gegenbild entgegenzuhalten: den Körper als perfekte Konstruktion. Auch das wäre ein Fehler — nicht weil der Körper schlecht wäre, sondern weil „perfekt" der falsche Maßstab ist. Perfektion ist ein Ingenieursbegriff: reibungslos, optimal, fehlerfrei. Ein Organismus ist nichts davon, und er muss es nicht sein. Die vielzitierten „Konstruktionsfehler" — der blinde Fleck im Auge, der gekreuzte Weg von Atem und Nahrung, die Wirbelsäule, die den aufrechten Gang teuer bezahlt — sind keine Pfuscharbeit. Es sind Kompromisse, und Kompromisse sind die Signatur eines Anpassungsprozesses, der unter realen Bedingungen arbeitet, nicht am Reißbrett. Gemessen an der einzigen Aufgabe, die ein lebendiger Körper hat — zu leben, zu wachsen, sich zu erhalten, sich zu reparieren, eine Lebensspanne lang —, ist er erstaunlich gut. Wir sind nicht annähernd so mangelhaft, wie die Erlösungserzählung es braucht.</p>',
			'<p>Der entscheidende Punkt bleibt: Wer den Körper gegen den Vorwurf des Konstruktionsfehlers verteidigt, indem er ihn perfekt nennt, hat die Prämisse der Transhumanisten schon übernommen — den Körper überhaupt als Konstruktion zu beurteilen, als gelungene oder misslungene Maschine. Der Körper ist keine Maschine, weder eine gute noch eine schlechte. Er ist ein lebendiger Prozess. Seine Intelligenz und seine Sterblichkeit sind nicht zwei Eigenschaften, sondern eine einzige: Was sich selbst organisiert, wächst und anpasst, ist eben dadurch an Stoffwechsel, Reibung, Verschleiß und Endlichkeit gebunden. Eine Maschine lässt sich im Prinzip endlos reparieren, weil sie nur aus Teilen besteht. Ein Organismus nicht — weil er lebt.</p>',
			'<p>Hier ist Vorsicht geboten — in zwei Richtungen. Es wäre dogmatisch zu behaupten, Bewusstsein könne unter keinen Umständen ohne Biologie existieren. Das Phänomen des Bewusstseins ist ungelöst. Der Philosoph David Chalmers nennt es das „harte Problem": Niemand kann erklären, warum Informationsverarbeitung überhaupt von subjektivem Erleben begleitet wird. Ob es nicht-biologische Formen von Bewusstsein geben kann, weiß niemand. Aber genau diese Offenheit ist der Punkt. Der Transhumanismus behandelt die Frage nicht als offen — er verkauft eine Antwort. Er behauptet nicht nur, irgendein Bewusstsein sei denkbar, sondern: dein konkretes Ich, deine Erinnerungen, deine Person ließen sich kopieren und fortsetzen. Das ist kein mutiger Blick in die Wissenschaft. Das ist eine Spekulation im Tonfall der Ingenieurskunst.</p>',
			'<p>Und selbst wenn die Technik eines Tages gelänge, bliebe ein Einwand, den der Philosoph Derek Parfit 1984 unwiderlegt formuliert hat. Man stelle sich ein Gerät vor, das einen Menschen scannt, das Original zerstört und anderswo eine atomgenaue Kopie erzeugt. Die Kopie erinnert sich an alles, hält sich für dieselbe Person. Aber bliebe das Original am Leben, stünden sich zwei Menschen gegenüber, nicht einer. Ähnlichkeit ist keine Identität. Eine perfekte digitale Kopie meiner Person wäre ein beeindruckendes Archiv — ein interaktives Denkmal, eine Täuschung für die Hinterbliebenen. Sie wäre nicht die Fortsetzung meines erlebten Bewusstseins. Wer das verspricht, überwindet den Tod nicht. Er kaschiert ihn. Digitale Unsterblichkeit ist keine Auferstehung, sondern Nachlassverwaltung mit Benutzeroberfläche.</p>',
			'<p>Dass diese Skepsis nicht nur philosophisch, sondern auch naturwissenschaftlich begründet ist, zeigt ein nüchterner Blick auf die Forschung. Seit über einem Jahrzehnt versucht das internationale Projekt OpenWorm, das Nervensystem des Fadenwurms <em>Caenorhabditis elegans</em> vollständig digital nachzubilden — einen der einfachsten Organismen überhaupt, mit exakt 302 Neuronen, jede Verbindung kartiert. Es ist bis heute nicht gelungen, diesen Wurm so zu emulieren, dass er sich verhält wie sein lebendiges Vorbild. 302 Neuronen. Das menschliche Gehirn hat rund 86 Milliarden. Wer einen Wurm nicht hochladen kann, sollte vom Upload des Menschen schweigen.</p>',
			'<p>Es gibt einen weiteren Verlust, den das technokratische Denken übersieht: die Bedeutung der Form. Die materielle Welt ist für den Menschen kein neutraler Trägerstoff. Unsere Kreativität, unsere Kunst, unser Denken entzünden sich am Widerstand und an der Gestalt der physischen Welt — an der Maserung des Holzes, der Geometrie des Wachsenden, dem Gewicht des Steins. Ein Bewusstsein in der Cloud verlöre nicht nur seine biologische Resonanz, das Klopfen des Herzens bei Angst, das Zusammenspiel von Berührung und Bindung. Es verlöre den schöpferischen Dialog mit der Materie selbst. Es wäre kein befreiter Geist, sondern ein Selbstgespräch in sensorischer Isolation.</p>',
		],
		'III. Die Angst vor dem Unverfügbaren' => [
			'<p>Warum hält sich eine so fragile Utopie ausgerechnet bei den einflussreichsten Menschen des Planeten? Die Antwort liegt weniger in der Wissenschaft als in einer kulturellen Disposition.</p>',
			'<p>Erich Fromm hat der Biophilie — der Zuneigung zum Lebendigen, Wachsenden, Unberechenbaren — die Nekrophilie gegenübergestellt: die Neigung zum Mechanischen, Toten, restlos Kontrollierbaren. Man muss daraus keine Ferndiagnose einzelner Personen machen, um den kulturellen Sog zu erkennen. Er hat einen Kern: die Unfähigkeit, das Unverfügbare auszuhalten — all das, was sich grundsätzlich nicht herstellen, steuern oder optimieren lässt. Das Lebendige zeichnet sich gerade dadurch aus, dass es sich der vollständigen Kontrolle entzieht. Es altert, erkrankt, stirbt, lässt sich nicht in Metriken pressen.</p>',
			'<p>Für eine Kultur, die gelernt hat, dass sich jedes Problem mit dem richtigen Algorithmus, genug Rechenleistung und genug Kapital lösen lässt, ist diese Unverfügbarkeit eine Kränkung. Der eigene Tod ist das eine, was sich nicht skalieren, nicht optimieren, nicht bestechen lässt. Die Flucht ins Verjüngungslabor und in die digitale Unsterblichkeit ist der Versuch, das Lebendige so lange in Daten zu übersetzen, bis es endlich berechenbar ist.</p>',
			'<p>Hier verschiebt sich etwas Entscheidendes. Der Transhumanismus hört auf zu fragen, was Technik dem Menschen ermöglichen kann, und beginnt zu behaupten, wohin der Mensch sich zu entwickeln habe. An diesem Punkt wird aus einer Forschungsagenda eine Weltanschauung — eine, die das Organische zum Provisorium erklärt und das Mechanische zur Verheißung. Sie ist deshalb nicht automatisch falsch. Aber sie ist unehrlich, wenn sie sich als bloße Wissenschaft ausgibt. Wissenschaft beschreibt, prüft, verwirft. Eine Heilsbotschaft verspricht eine Richtung der Geschichte.</p>',
		],
		'IV. Die soziale Demaskierung' => [
			'<p>Holt man das transhumanistische Projekt aus der philosophischen Höhe auf den Boden der Gesellschaft, verliert es seine humanitäre Maske vollends.</p>',
			'<p>Lebenszeit ist schon heute ungleich verteilt. Eine vielzitierte Studie des Ökonomen Raj Chetty, 2016 im <em>Journal of the American Medical Association</em> veröffentlicht und auf 1,4 Milliarden Steuerdatensätzen beruhend, zeigt: Zwischen dem reichsten und dem ärmsten Prozent der US-Bevölkerung liegt eine Lücke in der Lebenserwartung von etwa fünfzehn Jahren bei Männern und zehn bei Frauen. Aber — und das ist entscheidend — diese Lücke ist kein biologisches Rätsel, das nach Gen-Scheren und Longevitäts-Pillen verlangt. Sie ist das Ergebnis ungleicher Lebensbedingungen: chronischer Stress, schlechtere Ernährung, härtere Arbeit, weniger Schlaf, weniger Sicherheit. Sie schließt sich nicht durch Technologie, sondern durch Verteilung.</p>',
			'<p>Das ist der eigentliche Befund. Die wirksamsten Mittel für ein langes, gesundes Leben sind längst bekannt und zutiefst unspektakulär: Sicherheit, Ruhe, gute Arbeit, soziale Bindung, das Gefühl, nicht permanent ausgenutzt zu werden. Eine gerechtere Gesellschaft verlängert das Leben vieler Menschen — ganz ohne Silizium. Eine marktförmig organisierte Lebensverlängerung dagegen verteilt Lebenszeit nicht um; sie legt sich auf ein bestehendes Gefälle und macht es steiler. Und der Reiche, der sich mit Transfusionen, Gentherapien und Pillen versorgt, wird dadurch nicht zu einem höheren Menschen. Er wird zu einem hyper-medizinisierten Exponat. Seine Existenz wird nicht tiefer, sondern steriler.</p>',
			'<p>Hinter dem Drang nach technischer Unsterblichkeit steht zudem das Symptom einer erschöpften Kultur. Wohlstand allein macht nicht glücklich: Die USA, eines der reichsten Länder der Erde, sind im World Happiness Report 2025 auf den 24. Platz gefallen — den niedrigsten Wert seit Beginn der Erhebung. Soziologen sprechen von „deaths of despair", von wachsender Vereinsamung, von einer Zunahme psychischer Erkrankungen mitten im materiellen Überfluss. Der Mensch funktioniert in einem hyperkompetitiven System aus Konsumdruck und permanenter Konkurrenz nicht mehr richtig — und statt die krankmachenden Strukturen zu reparieren, bietet das Silicon Valley die Flucht in die künstliche Ewigkeit an.</p>',
			'<p>Hier liegt ein tieferer Trugschluss. Die transhumanistische Flucht setzt voraus, dass das Leben selbst — verkörpert, endlich, sterblich — so mangelhaft sei, dass der Ausstieg die vernünftige Antwort wäre. Das ist Pessimismus im strengen Sinn: das Urteil, Leiden sei der Grundton des Daseins. Arthur Schopenhauer hat diese Position so klar formuliert wie kaum ein anderer. Aber sie verwechselt zwei Dinge. Die Erschöpfung, die Vereinsamung, das Gefühl, verbraucht zu werden, sind real — doch sie sind nicht die metaphysische Signatur des Menschseins. Sie sind das Ergebnis bestimmter, historisch gemachter Macht- und Wirtschaftsverhältnisse. Damit begeht der Transhumanismus einen zweiten Kategorienfehler: Nachdem er den Menschen mit einer Maschine verwechselt hat, verwechselt er nun ein politisches Problem mit einem existenziellen. Er hält ein krankes System für eine kranke Existenz. Das ist nicht nur ungenau, es ist folgenreich — denn ein krankes System lässt sich ändern, eine kranke Existenz nicht. Wer erkennt, dass das Unbehagen gemacht wurde, kann es auch ungemacht denken. Eine bewohnbare Welt ist keine naive Hoffnung, sondern eine politische Möglichkeit — und gerade sie verstellt der Fluchtgedanke.</p>',
			'<p>Diese Flucht entlastet. Wer glaubt, bald auf einem Server fortzuleben, muss sich um den Verfall des Sozialstaats, die Einsamkeit in den Pflegeheimen, die Würde des Sterbens weniger kümmern. Die Obszönität des Transhumanismus liegt darin, dass er das Falsche zuerst will: die Verlängerung des Lebens einiger weniger, bevor die Lebensbedingungen der vielen gesichert sind.</p>',
		],
		'V. Der Gegenentwurf: Sorge und Endlichkeit' => [
			'<p>Der Gegenentwurf ist keine Technikfeindlichkeit. Wir müssen nicht kleiner von der Technik denken, sondern größer vom Menschen. Fortschritt bemisst sich dann nicht daran, wie weit wir uns von unserer biologischen Natur entfernen, sondern wie tief wir ihr gerecht werden. Drei Richtungen, konkret.</p>',
			'<p><strong>Erstens: die Aufwertung der Sorge.</strong> Ein System, das Milliarden für die Abschaffung des Todes mobilisiert, aber Pflegekräfte am Mindestlohn hält, ist moralisch in Schieflage. Die Antwort auf Verletzlichkeit ist nicht ihre Abschaffung, sondern Zuwendung — Pflege, Erziehung, Palliativmedizin gehören ins Zentrum der Gesellschaft, nicht an ihren Rand. Eine Zivilisation zeigt ihren Rang nicht daran, wie alt ihre Milliardäre werden, sondern daran, wie sie mit ihren Schwächsten umgeht.</p>',
			'<p><strong>Zweitens: die Anerkennung der Endlichkeit.</strong> Der Philosoph Bernard Williams hat 1973 in seinem Essay über den „Fall Makropulos" gezeigt, warum ein unendliches Leben nicht erstrebenswert, sondern unerträglich wäre: Was unsere Wünsche, Bindungen und Entscheidungen mit Bedeutung auflädt, ist ihre Verknüpfung mit einem endlichen Leben. Unendlichkeit ist Stillstand. Erst weil Zeit begrenzt ist, hat sie Gewicht; erst weil wir verschwinden, ist es nicht gleichgültig, wie wir leben. Eine Palliativmedizin, die Schmerz lindert und ein würdiges Sterben in Gemeinschaft ermöglicht, ist humaner als die Verlängerung des bloßen biologischen Funktionierens um jeden Preis.</p>',
			'<p><strong>Drittens: der Schutz der analogen Lebenswelt.</strong> Der Philosoph Yuk Hui erinnert mit dem Begriff der „Kosmotechnik" daran, dass Technik immer in eine Ordnung eingebettet ist — in Beziehung zu Körper, Ort, Gemeinschaft und Natur. Eine humane technologische Kultur baut Städte, Schulen und Räume, in denen der Körper Heimat findet, statt den Menschen in die sensorische Verarmung der Bildschirme zu treiben. Sie fragt nicht nur, was machbar ist, sondern in welche Ordnung das Machbare gehört.</p>',
			'<p>Der Mensch ist kein defektes Gerät. Der Körper ist kein Gefängnis. Bewusstsein ist keine Datei. Sterblichkeit ist kein Softwarefehler — sie ist das Gesetz des Lebendigen.</p>',
			'<p>Eine Maschine ist ein fertiges Produkt. Ein Mensch ist es nie. Wir sind das Ergebnis von fast vier Milliarden Jahren ununterbrochenen Werdens — und auch das einzelne Leben bleibt ein Werden, bis zuletzt. Ein Neugeborenes ist keine leere Festplatte: Es trägt ein tiefes biologisches Erbe in sich, die Anlage zur Sprache, zum aufrechten Gang, zur Zuwendung. Aber es ist ebenso wenig ein fertiges Programm. Es entfaltet sich erst — lernt, wächst, wird, im Kontakt mit der Welt, mit Sprache, mit anderen Menschen. Und dieses Werden endet nicht mit der Kindheit. Das Gehirn bleibt ein Leben lang formbar; der Mensch lernt, verlernt und verwandelt sich bis ins Alter. Es gibt kein Wesen, das wir kennen, das sich selbst so weitreichend umbauen kann — ganz ohne Silizium.</p>',
			'<p>Diese Unfertigkeit ist der entscheidende Punkt. Der Transhumanismus liest sie als Mangel, den man wegoptimieren müsse — als Fehler auf dem Weg zum fertigen, perfekten, abgeschlossenen Menschen. Aber ein Lebewesen fertigzustellen heißt, es anzuhalten. Das Unvollendete, das Noch-nicht, die offene Lücke sind nicht der Defekt des Menschen. Sie sind die Bedingung von Wachstum, von Kreativität, von Freiheit. Den Menschen für ein abgeschlossenes, überholtes Modell zu erklären, ist deshalb kein kühner Schritt nach vorn — es ist ein Mangel an Vorstellungskraft. Wer den Menschen vollendet, überwindet ihn nicht; er friert ihn ein.</p>',
			'<p>Der transhumanistische Traum vom ewigen Leben ist deshalb kein mutiger Blick nach vorn. Er ist die Weigerung, dieses eine, unfertige, endliche Leben so zu gestalten, dass es nicht permanent nach Flucht verlangt. Die eigentliche Aufgabe ist nicht, den Menschen abzuschaffen. Sie ist, eine Welt zu bauen, in der das Unfertige nicht als Mangel erlitten werden muss, sondern als das gelebt werden kann, was es in Wahrheit ist: unsere Freiheit, noch nicht fertig zu sein.</p>',
		],
	];

	$sources = [
		'<strong>Altos Labs</strong> — 2022 mit 3 Mrd. USD Startfinanzierung gegründet (u. a. Jeff Bezos, Yuri Milner, ARCH Venture Partners); wissenschaftlicher Berater ist der Nobelpreisträger Shinya Yamanaka.',
		'<strong>Retro Biosciences</strong> — gegründet mit Seed-Kapital von Sam Altman; Series-A-Runde mit angestrebter Bewertung von ca. 5 Mrd. USD; erklärtes Ziel: zehn zusätzliche gesunde Lebensjahre.',
		'<strong>Ray Kurzweil</strong> — <em>The Singularity Is Near</em>, 2005; prognostizierte „Singularität" um 2045.',
		'<strong>„Über 100.000 altersbedingte Tote täglich"</strong> — gängige Schätzung aus dem Longevity-Diskurs (u. a. Aubrey de Grey): Von weltweit rund 150.000–170.000 Todesfällen pro Tag wird der überwiegende Teil altersbedingten Ursachen zugerechnet. Die Zahl wird hier als Argument der Gegenseite zitiert.',
		'<strong>Maurice Merleau-Ponty</strong> — <em>Phänomenologie der Wahrnehmung</em>, 1945 (Unterscheidung Leib/Körper).',
		'<strong>Verkörperte Kognition</strong> — u. a. Varela, Thompson, Rosch, <em>The Embodied Mind</em>, 1991; Lakoff & Johnson, <em>Philosophy in the Flesh</em>, 1999.',
		'<strong>Darm-Hirn-Achse / Mikrobiom</strong> — etablierter Forschungsstand zur Wechselwirkung von Mikrobiom, Immun- und Nervensystem.',
		'<strong>Evolutionäre Kompromisse</strong> — der menschliche Körper als Resultat von Trade-offs (u. a. Wirbelsäule und aufrechter Gang, blinder Fleck der Netzhaut, gekreuzter Atem- und Schluckweg); Standardthema der Evolutionsbiologie.',
		'<strong>David Chalmers</strong> — „Facing Up to the Problem of Consciousness", 1995 (das „harte Problem des Bewusstseins").',
		'<strong>Derek Parfit</strong> — <em>Reasons and Persons</em>, 1984 (Teletransportation, Problem der personalen Identität).',
		'<strong>OpenWorm</strong> — seit ca. 2011 laufendes Open-Science-Projekt zur digitalen Emulation des Nervensystems von <em>C. elegans</em> (302 Neuronen).',
		'<strong>Erich Fromm</strong> — Unterscheidung Biophilie/Nekrophilie, in <em>The Heart of Man</em>, 1964, und <em>The Anatomy of Human Destructiveness</em>, 1973.',
		'<strong>Raj Chetty et al.</strong> — „The Association Between Income and Life Expectancy in the United States, 2001–2014", <em>JAMA</em> 2016; 1,4 Mrd. Steuerdatensätze.',
		'<strong>World Happiness Report 2025</strong> — Finnland Platz 1; USA auf Platz 24, niedrigster Wert seit Beginn der Erhebung.',
		'<strong>Bernard Williams</strong> — „The Makropulos Case: Reflections on the Tedium of Immortality", in <em>Problems of the Self</em>, 1973.',
		'<strong>Yuk Hui</strong> — <em>The Question Concerning Technology in China: An Essay in Cosmotechnics</em>, 2016.',
	];

	$separator = "<!-- wp:separator -->\n<hr class=\"wp-block-separator has-alpha-channel-opacity\"/>\n<!-- /wp:separator -->\n\n";

	$out  = "<!-- wp:paragraph -->\n<p><em>" . $lead . "</em></p>\n<!-- /wp:paragraph -->\n\n";
	$out .= $separator;

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
