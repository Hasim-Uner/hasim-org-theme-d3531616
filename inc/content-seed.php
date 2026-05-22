<?php
/**
 * Content Seed — Hasimuener Journal
 *
 * Idempotente Erstanlage redaktioneller Inhalte (Essay + Glossar).
 * Läuft einmalig pro Seed-Version im Admin-Kontext und legt Posts an,
 * sofern noch nicht vorhanden (Match per Slug). Bestehende Inhalte
 * werden NICHT überschrieben.
 *
 * Erweiterung: Neue Seeds als zusätzliche Version (HP_CONTENT_SEED_VERSION
 * inkrementieren) registrieren — Admin-init prüft den Versions-Flag und
 * führt den Seed genau einmal aus.
 *
 * @package Hasimuener_Journal
 * @since   6.5.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Aktuelle Seed-Version. Inkrementieren, sobald neue Inhalte ergänzt werden.
 */
const HP_CONTENT_SEED_VERSION = '2026-05-22-transhumanismus';

/**
 * Trigger: einmaliger Seed-Run im Admin-Kontext.
 */
function hp_run_content_seed_once(): void {
	if ( ! is_admin() ) {
		return;
	}

	if ( get_option( 'hp_content_seed_version' ) === HP_CONTENT_SEED_VERSION ) {
		return;
	}

	hp_seed_transhumanismus_essay();
	hp_seed_transhumanismus_glossary();

	update_option( 'hp_content_seed_version', HP_CONTENT_SEED_VERSION, false );
}
add_action( 'admin_init', 'hp_run_content_seed_once', 20 );

/**
 * Legt den Essay „Abrechnung mit dem Transhumanismus" idempotent an.
 */
function hp_seed_transhumanismus_essay(): void {
	$slug = 'abrechnung-transhumanismus';

	$existing = get_page_by_path( $slug, OBJECT, 'essay' );
	if ( $existing instanceof WP_Post ) {
		return;
	}

	$content = hp_get_transhumanismus_essay_content();

	$post_id = wp_insert_post( [
		'post_type'    => 'essay',
		'post_status'  => 'publish',
		'post_name'    => $slug,
		'post_title'   => 'Abrechnung mit dem Transhumanismus: Geist vs. Excel-Tabelle',
		'post_excerpt' => 'Warum der transhumanistische Traum vom ewigen Leben kein Fortschritt ist, sondern die Endstufe eines reduktionistischen Weltbildes — und der Verrat am lebendigen Geist.',
		'post_content' => $content,
	], true );

	if ( is_wp_error( $post_id ) || ! $post_id ) {
		return;
	}

	// Social-Teaser für Automation (REST/X).
	update_post_meta(
		$post_id,
		'_hp_social_teaser',
		'Der Transhumanismus ist keine Wissenschaft — er ist die säkulare Ersatzreligion einer hyperreichen Elite, die das Leben mit einer Excel-Tabelle verwechselt.'
	);

	// Topic-Taxonomie (sofern vorhanden) — bewusst weich, kein Fehler bei fehlendem Term.
	if ( taxonomy_exists( 'topic' ) ) {
		wp_set_object_terms( $post_id, [ 'Gesellschaft', 'Technologie', 'Philosophie' ], 'topic', false );
	}
}

/**
 * Liefert den HTML-Inhalt des Transhumanismus-Essays.
 */
function hp_get_transhumanismus_essay_content(): string {
	return <<<'HTML'
<!-- wp:paragraph -->
<p><em>Wenn die Tech-Elite über die Abschaffung des Todes schwadroniert, verkauft sie uns das als wissenschaftlichen Fortschritt. In Wahrheit ist es eine säkulare Ersatzreligion für Hyperreiche, die vor lauter narzisstischer Verlustangst panisch vor ihrer eigenen Vergänglichkeit flüchten.</em></p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>1. Die neue säkulare Religion der Milliardäre</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Wenn die Tech-Elite über die Abschaffung des Todes schwadroniert, verkauft sie uns das als wissenschaftlichen Fortschritt. In Wahrheit ist der Transhumanismus nichts weiter als eine säkulare Ersatzreligion für Hyperreiche, die vor lauter narzisstischer Verlustangst panisch vor ihrer eigenen biologischen Vergänglichkeit flüchten. Weil sie unfähig sind, dem Leben im Hier und Jetzt einen Sinn jenseits von nacktem Besitz und unendlicher Akkumulation zu geben, erklären sie ihren persönlichen Kontrollzwang zur evolutionären Pflicht für die gesamte Menschheit. Der Unterbau zahlt den Preis für die Psychosen einer Handvoll Privilegierter, während die reale Welt im Eiltempo verrottet.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>2. Der historische Sündenfall: Die Welt als tote Maschine</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Das bizarre Verständnis von Intelligenz, das die heutige Tech-Elite an den Tag legt, ist kein neues Phänomen, sondern das Endstadium eines jahrhundertealten, methodischen Fehlers der westlichen Wissenschaft. Seit der Aufklärung wurde die Welt radikal gespalten: in den menschlichen Geist und die angeblich „tote" Materie. Die Natur wurde zur seelenlosen Maschine degradiert, die man bloß berechnen, zerlegen und beherrschen muss.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Genau diese verengte Sichtweise feiert im Transhumanismus ihre absurde Wiederauferstehung. Wenn diese Leute von „Intelligenz" oder „Bewusstsein" reden, meinen sie nichts weiter als sequenzielle Logik, Algorithmen und optimierte Datenverarbeitung — das Leben reduziert auf das Niveau einer Excel-Tabelle. Sie sind blind für die Tatsache, dass das Dasein an sich eine inhärente, lebendige Intelligenz besitzt, die in einem einzigen Baum komplexere organische Alchemie betreibt als in jedem Hochleistungsrechner.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Diese ideologische Verengung ist historisch nachweisbar die Wurzel unserer größten Krisen: Wer die Welt und das Leben nur als Rechenmasse begreift, für den sind Klimakatastrophen und die industrielle Kälte moderner Kriege nur statistische Kollateralschäden eines fehlerhaften Systems. Die Tech-Elite versucht nicht, das Bewusstsein zu erweitern — sie hat schlicht verlernt, das Wunder des lebendigen Geistes überhaupt noch zu begreifen.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>3. Das Wunder des Organischen: Warum die Schöpfung größer ist als das Werkzeug</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Die Schöpfung kann niemals größer sein als der Schöpfer. Dass KI überhaupt existiert, ist kein Beweis für das baldige Ende des Menschen, sondern das ultimative Zeugnis für die unendliche Genialität des menschlichen Geistes. Die Maschine ist nichts weiter als ein winziger, abgeleiteter Funke unserer eigenen Schöpferkraft. Die Transhumanisten drehen dieses Verhältnis in ihrer Verblendung komplett um: Sie stempeln den Menschen zum fehlerhaften Mängelwesen ab, das durch sterile Technologie optimiert werden muss, während sie vor einem Haufen Silizium auf die Knie fallen.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Dabei ist der Mensch die komplexeste und wunderbarste Technologie, die das bekannte Universum je hervorgebracht hat. Unsere wahre Natur besteht darin, lebendigen Geist und abstrakte Ideen in physische Materie zu verwandeln — das ist ein Akt echter, organischer Magie, den keine sequentielle Datenverarbeitung jemals replizieren kann. Selbst ein einfacher Baum betreibt eine so unfassbar komplexe biologische Alchemie, dass jeder Supercomputer dagegen wie ein klobiges Kinderspielzeug wirkt. Wer das Leben versteht, begreift, dass alles, was wir erschaffen — inklusive der KI —, zutiefst menschlich ist. Die wahre Intelligenz steckt nicht im Algorithmus, sondern in der lebendigen Substanz, die ihn hervorgebracht hat.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>4. Die systemische Geiselnahme: Psychosen auf Kosten des Unterbaus</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Während eine Handvoll Tech-Milliardäre astronomische Summen in die Erforschung obskurer Verjüngungsmethoden und digitaler Unsterblichkeit pumpt, lassen sie die reale Welt im Eiltempo verrotten. Diese Fixierung auf eine fiktive Zukunft ist das ultimative Ablenkungsmanöver: Es entzieht den Überbau der Verantwortung für das konkrete Hier und Jetzt — für kollabierende Bildungssysteme, soziale Ungleichheit und die reale Zerstörung unserer Umwelt.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Die systematische Verdummung der Massen ist dabei kein Versehen, sondern eine zwingende Systemvoraussetzung. Durch algorithmische Reizüberflutung, digitale Sedierung und den permanenten Konsumrausch im Internet wird der Unterbau bewusst stumpf gehalten. Ein abgelenkter, psychisch isolierter Mensch begehrt nicht gegen die Entstehung einer neuen, technokratischen Klassengesellschaft auf. Die Elite ist absolut blind für die Belastungsgrenzen des echten Lebens, weil sie ihre eigenen mathematischen Modelle für unfehlbar hält. Sie bauen digitale Pyramiden für ihr eigenes Ego, während die gesellschaftliche Basis unter ihren Füßen wegbricht.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>5. Fazit: Wahre Demut statt technologischer Größenwahn</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Das Heilen der Gegenwart ist die eigentliche schöpferische Aufgabe des Menschen, nicht die feige Flucht in eine fiktive, unendliche Zukunft. Der transhumanistische Traum vom ewigen Leben im digitalen Käfig ist ein fundamentaler Trugschluss, der die biologische und philosophische Realität komplett verkennt. Ohne die unerbittliche Dringlichkeit unserer Sterblichkeit verliert jede Entscheidung im Hier und Jetzt ihr Gewicht. Erst der absolute Endpunkt des Todes verleiht der menschlichen Existenz echte Tragik, Tiefe und den notwendigen Funken für wahre, lebendige Kreativität. Unendliche Fortexistenz als Datenstrom wäre kein Triumph, sondern der absolute, sterile Stillstand.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Wahre Demut bedeutet nicht, sich vor künstlichen Göttern aus Silizium zu beugen, sondern das Mysterium des Bewusstseins wieder in seiner holistischen, organischen Ganzheit zu begreifen. Wir müssen aufhören, das Leben an die starren Vorgaben zerstörerischer, unnatürlicher Systeme anzupassen. Es ist an der Zeit, unsere unendliche Fähigkeit — Geist in Materie zu verwandeln — dafür zu nutzen, die Deformationen der Gegenwart radikal zu reparieren. Das Leben ist keine fehlerhafte Software, die optimiert werden muss, sondern das größte Wunder des Universums, das im Hier und Jetzt gelebt werden will.</p>
<!-- /wp:paragraph -->
HTML;
}

/**
 * Legt vier Glossar-Einträge zum Transhumanismus-Komplex idempotent an.
 */
function hp_seed_transhumanismus_glossary(): void {
	$entries = [
		[
			'slug'  => 'reduktionismus',
			'title' => 'Reduktionismus (methodischer)',
			'kurz'  => 'Der fundamentale Fehler der modernen westlichen Wissenschaft seit Descartes, das Lebendige und das Bewusstsein rein als Summe mechanischer, berechenbarer Einzelteile zu betrachten. Ignoriert die holistische Komplexität biologischer und kosmischer Systeme.',
			'long'  => 'Der methodische Reduktionismus zerlegt komplexe Systeme so lange in immer kleinere Bestandteile, bis nur noch berechenbare Einzelteile übrig bleiben. Dieses Verfahren ist für Mechanik und Chemie nützlich, scheitert aber spektakulär an lebendigen, emergenten Systemen — vom Ökosystem bis zum Bewusstsein. Im Transhumanismus feiert dieser Denkfehler seine Wiederauferstehung: Geist wird zur Datenverarbeitung, Leben zur Excel-Tabelle.',
		],
		[
			'slug'  => 'transhumanismus',
			'title' => 'Transhumanismus',
			'kurz'  => 'Eine im Silicon Valley verwurzelte ideologische Bewegung, die die Überwindung der biologischen Grenzen des Menschen (Altern, Tod) durch Technologie anstrebt. Agiert faktisch als säkulare Ersatzreligion für eine hyperreiche Elite zur Betäubung von existenzieller Verlustangst.',
			'long'  => 'Der Transhumanismus verspricht die technologische Überwindung von Krankheit, Altern und Tod — durch Genetik, KI, Neuro-Interfaces und „Mind Uploading". Hinter der wissenschaftlichen Fassade steht eine eschatologische Heilserwartung, die strukturell religiösen Erlösungsnarrativen folgt, ohne deren ethische Selbstreflexion zu übernehmen. Faktisch dient die Bewegung als psychologisches Bewältigungssystem für eine hyperreiche Klasse, die ihre eigene Sterblichkeit nicht akzeptieren kann.',
		],
		[
			'slug'  => 'kosmotechnik',
			'title' => 'Kosmotechnik',
			'kurz'  => 'Ein von Yuk Hui geprägter Begriff. Der Gegenentwurf zur linearen, westlichen Zerstörungstechnologie. Er fordert, dass technische Entwicklungen wieder an die jeweilige Kultur, Natur und kosmische Ordnung zurückgebunden werden, statt das Universum als tote Rechenmasse auszubeuten.',
			'long'  => 'Yuk Huis Kosmotechnik-Begriff hinterfragt die Annahme, Technik sei kulturell neutral und universal. Stattdessen schlägt er vor, technische Entwicklungen als kulturell, kosmologisch und ökologisch gebundene Praktiken zu verstehen — als Verbindung zwischen Mensch, Natur und kosmischer Ordnung. Damit liefert die Kosmotechnik einen philosophischen Gegenentwurf zum extraktiven, reduktionistischen Technikverständnis des Silicon Valley.',
		],
		[
			'slug'  => 'biophobie',
			'title' => 'Biophobie',
			'kurz'  => 'Die pathologische Angst vor der Unberechenbarkeit, Vergänglichkeit und Fleischlichkeit des organischen Lebens, die sich im transhumanistischen Drang äußert, alles Lebendige in sterile, kontrollierbare Datensätze (Silizium) zu pressen.',
			'long'  => 'Biophobie beschreibt eine kulturelle und psychische Aversion gegen das Lebendige selbst: gegen seine Unkontrollierbarkeit, sein Werden und Vergehen, seine Fleischlichkeit. Im transhumanistischen Diskurs zeigt sich diese Haltung in der Sehnsucht, organische Prozesse durch digitale, „saubere" Substitute zu ersetzen — vom synthetischen Embryo bis zum geuploadeten Geist. Biophobie ist damit nicht bloß Angst, sondern ein politisches Programm der Naturverwerfung.',
		],
	];

	foreach ( $entries as $entry ) {
		$existing = get_page_by_path( $entry['slug'], OBJECT, 'glossar' );
		if ( $existing instanceof WP_Post ) {
			continue;
		}

		$post_id = wp_insert_post( [
			'post_type'    => 'glossar',
			'post_status'  => 'publish',
			'post_name'    => $entry['slug'],
			'post_title'   => $entry['title'],
			'post_excerpt' => $entry['kurz'],
			'post_content' => "<!-- wp:paragraph -->\n<p>" . esc_html( $entry['long'] ) . "</p>\n<!-- /wp:paragraph -->",
		], true );

		if ( is_wp_error( $post_id ) || ! $post_id ) {
			continue;
		}

		update_post_meta( $post_id, '_hp_glossar_kurz', $entry['kurz'] );
	}
}
