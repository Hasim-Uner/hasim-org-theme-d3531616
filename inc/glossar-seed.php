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

const HP_GLOSSAR_SEED_VERSION = '2026-06-02-glossar-r11-sterblichkeit-korrekturen';

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
			'kurz'     => 'Transhumanismus ist die Ideologie, den Menschen durch Technologie über seine biologischen Grenzen hinaus zu optimieren. Im Kern verwechselt er Endlichkeit mit einem technischen Defekt.',
			'content'  => [
				'Transhumanismus erscheint oft als Zukunftsvision, ist aber zuerst ein Menschenbild. Der Körper gilt ihm als unzureichende Hardware, das Bewusstsein als übertragbare Information und der Tod als ein lösbares Ingenieursproblem.',
				'Darin liegt seine eigentliche Radikalität: Nicht einzelne Krankheiten sollen geheilt werden, sondern die conditio humana selbst soll überwunden werden. Verletzlichkeit, Alter, Abhängigkeit und Sterblichkeit erscheinen nicht mehr als Bedingungen menschlicher Existenz, sondern als peinliche Restbestände biologischer Vergangenheit.',
				'Kritisch betrachtet ist Transhumanismus deshalb weniger Wissenschaft als säkulare Erlösungslehre. Er verspricht Rettung durch Technik, verschweigt aber, dass Sinn, Verantwortung und Bindung gerade aus Grenzen entstehen.',
			],
			'synonyme' => [ 'transhumanistische', 'transhumanistischen', 'transhumanistischer', 'transhumanistischem', 'transhumanistisches' ],
		],
		[
			'slug'     => 'reduktionismus-methodischer',
			'title'    => 'Reduktionismus (methodischer)',
			'kurz'     => 'Methodischer Reduktionismus beschreibt die Zerlegung komplexer Wirklichkeit in isolierte Einzelteile. Problematisch wird er, wenn diese Methode zur Weltanschauung wird.',
			'content'  => [
				'Reduktionismus ist als Werkzeug mächtig: Er macht Dinge messbar, zerlegbar und technisch bearbeitbar. Ohne ihn gäbe es keine moderne Medizin, keine Ingenieurskunst und keine exakte Naturwissenschaft.',
				'Sein Fehler beginnt dort, wo das Zerlegen mit dem Verstehen verwechselt wird. Ein lebendiger Organismus, ein Bewusstsein oder eine Gesellschaft ist nicht einfach die Summe seiner Bestandteile, sondern ein Geflecht aus Beziehungen, Rückkopplungen und Bedeutungen.',
				'In seiner ideologischen Form verengt Reduktionismus den Blick auf das, was berechenbar ist. Alles andere wirkt dann wie Störung: Leib, Gefühl, Geschichte, Kultur, Tod, Ambivalenz.',
			],
			'synonyme' => [ 'methodischer Reduktionismus', 'methodischen Reduktionismus', 'cartesianischer Dualismus', 'cartesianischen Dualismus' ],
		],
		[
			'slug'     => 'biophobie',
			'title'    => 'Biophobie',
			'kurz'     => 'Biophobie bezeichnet die Angst vor dem Lebendigen: vor Körperlichkeit, Unordnung, Verletzlichkeit und Vergänglichkeit. Sie zeigt sich im Wunsch, organisches Leben vollständig kontrollierbar zu machen.',
			'content'  => [
				'Biophobie ist keine bloße Abneigung gegen Natur. Sie ist tiefer: eine Abwehr gegen das, was sich nicht restlos planen, speichern, optimieren oder berechnen lässt.',
				'In technokratischen Weltbildern erscheint das Lebendige oft als Risiko. Körper altern, Beziehungen scheitern, Gefühle widersprechen sich, Ökosysteme reagieren unvorhersehbar. Genau diese Offenheit wird dann nicht als Wesensmerkmal des Lebens verstanden, sondern als Mangel.',
				'Der Begriff ist besonders wichtig zur Kritik transhumanistischer Fantasien. Dort wird Erlösung häufig als Flucht aus dem Organischen gedacht: weg vom Fleisch, weg vom Altern, weg vom Tod, hinein in eine sterile Ordnung aus Daten und Kontrolle.',
			],
			'synonyme' => [ 'transhumanistische Biophobie', 'transhumanistischen Biophobie' ],
		],
		[
			'slug'  => 'algorithmische-oeffentlichkeit',
			'title' => 'Algorithmische Öffentlichkeit',
			'kurz'  => 'Algorithmische Öffentlichkeit ist ein digitaler Raum, in dem Sichtbarkeit durch Plattformlogiken, Ranking-Systeme und Aufmerksamkeitsökonomie geprägt wird. Was relevant erscheint, ist nicht zwingend wichtig, sondern oft nur algorithmisch belohnt.',
			'content' => [
				'Öffentlichkeit war nie neutral. Doch in der algorithmischen Öffentlichkeit wird ihre Struktur von privaten Plattformen, Empfehlungslogiken und Engagement-Metriken tiefgreifend vorgeformt.',
				'Der gesellschaftliche Diskurs wird dadurch nicht einfach abgebildet, sondern sortiert, beschleunigt und emotionalisiert. Empörung, Lagerbildung und Wiederholung erhalten oft mehr Reichweite als Differenzierung, Geduld oder Verständigung.',
				'Das Problem liegt nicht nur im einzelnen Algorithmus, sondern im ökonomischen Rahmen. Wo Aufmerksamkeit verwertet wird, wird Öffentlichkeit zur Infrastruktur der Erregung.',
			],
		],
		[
			'slug'  => 'kosmotechnik',
			'title' => 'Kosmotechnik',
			'kurz'  => 'Kosmotechnik bezeichnet ein Denken von Technik, das nicht universalistisch und entgrenzt ist, sondern an Kultur, Natur und Weltverhältnis zurückgebunden bleibt.',
			'content' => [
				'Kosmotechnik steht gegen die Vorstellung, es gebe nur eine einzige technische Moderne. Technik ist nie bloß Werkzeug; sie trägt immer ein bestimmtes Verhältnis zur Welt in sich.',
				'Der Begriff macht sichtbar, dass technische Entwicklung nicht automatisch Fortschritt bedeutet. Eine Technologie kann produktiv sein und zugleich Lebensformen zerstören, Abhängigkeiten vertiefen oder kulturelle Ordnungen entwerten.',
				'Eine kosmotechnische Perspektive fragt deshalb nicht nur, was möglich ist. Sie fragt, in welche Welt eine Technik eingebettet ist, wem sie dient und welche Formen des Lebens sie stärkt oder beschädigt.',
			],
		],
		[
			'slug'  => 'kommune-grundzelle',
			'title' => 'Kommune (als Grundzelle)',
			'kurz'  => 'Die Kommune ist die kleinste politische Einheit gesellschaftlicher Selbstorganisation. Sie bildet den Ort, an dem Verantwortung, Versorgung und Entscheidung wieder konkret werden.',
			'content' => [
				'Als Grundzelle ist die Kommune kein romantisches Dorfideal, sondern ein politisches Prinzip. Entscheidungen sollen dort beginnen, wo ihre Folgen tatsächlich getragen werden.',
				'Gegen zentralistische Machtapparate setzt die Kommune auf Nähe, Überschaubarkeit und direkte Verantwortlichkeit. Sie verhindert nicht automatisch Herrschaft, aber sie erschwert ihre Abstraktion.',
				'In föderativen Modellen ist die Kommune der Ausgangspunkt aller höheren Strukturen. Räte, Netzwerke und Dachformen dürfen ihr nicht befehlen, sondern müssen aus ihr hervorgehen und ihr rechenschaftspflichtig bleiben.',
			],
		],
		[
			'slug'  => 'foederative-dacharchitektur',
			'title' => 'Föderative Dacharchitektur',
			'kurz'  => 'Föderative Dacharchitektur beschreibt ein horizontales Organisationsmodell, in dem koordinierende Strukturen den Basiseinheiten dienen, statt über sie zu herrschen.',
			'content' => [
				'Eine föderative Dacharchitektur verbindet dezentrale Einheiten, ohne sie in eine zentrale Befehlsordnung aufzulösen. Ihr Zweck ist Koordination, nicht Kontrolle.',
				'Der entscheidende Unterschied zu klassischer Hierarchie liegt im Mandat. Die Dachstruktur spricht nicht aus eigener Souveränität, sondern aus abgeleiteter Verantwortung gegenüber den verbundenen Basiseinheiten.',
				'Damit solche Modelle nicht selbst wieder bürokratische Machtzentren werden, brauchen sie klare Grenzen: Transparenz, Rückholbarkeit von Mandaten, rotierende Aufgaben und eine Kultur des Misstrauens gegenüber permanenter Funktionärsmacht.',
			],
		],
		[
			'slug'  => 'schicht-modell-infrastruktur-schichten',
			'title' => 'Schicht-Modell (Infrastruktur-Schichten)',
			'kurz'  => 'Das Schicht-Modell trennt stabile Basisinfrastruktur von flexiblen Anwendungsebenen. Es schützt Systeme davor, dass kurzfristige Akteure die Grundlagen dauerhaft vereinnahmen.',
			'content' => [
				'Das Schicht-Modell denkt Systeme nicht als ungeordnetes Ganzes, sondern als Ebenen mit unterschiedlicher Stabilität. Was unten liegt, muss robuster, langsamer und schwerer veränderbar sein als das, was oben experimentiert.',
				'In digitalen Systemen ist das sofort verständlich: Protokolle, Datenmodelle und Identitäten dürfen nicht beliebig von kurzfristigen Anwendungen abhängig sein. Sonst wird jedes System erpressbar durch seine Oberfläche.',
				'Politisch gelesen beschreibt das Modell eine Architektur der Autonomie. Die Grundinfrastruktur einer Gemeinschaft muss so gebaut sein, dass sie nicht bei jeder neuen Führung, Plattform oder Mode neu gekapert werden kann.',
			],
		],
		[
			'slug'  => 'jin-jiyan-azadi',
			'title' => 'Jin, Jiyan, Azadî (Frau, Leben, Freiheit)',
			'kurz'  => 'Jin, Jiyan, Azadî bedeutet Frau, Leben, Freiheit. Der Slogan verdichtet eine politische Ethik, in der Befreiung, Lebendigkeit und Geschlechtergerechtigkeit untrennbar verbunden sind.',
			'content' => [
				'Jin, Jiyan, Azadî ist mehr als ein Protest-Slogan. Die Formel verbindet die Befreiung der Frau mit der Befreiung des Lebens selbst und stellt damit patriarchale Herrschaft ins Zentrum der Kritik.',
				'Ihre Kraft liegt in der Einfachheit. Drei Begriffe bilden eine politische Grammatik: Ohne die Befreiung der Frau keine freie Gesellschaft, ohne Schutz des Lebens keine Freiheit, ohne Freiheit kein würdiges Leben.',
				'Im kurdischen Kontext steht der Satz für eine radikale Abkehr von Staat, Nation und Patriarchat als alleinigen Ordnungsformen. Er ist zugleich Erinnerung, Programm und moralischer Maßstab.',
			],
		],

		// --- Sterblichkeits- & Erkenntnistheorie-Komplex ---
		[
			'slug'     => 'conditio-humana',
			'title'    => 'Conditio humana',
			'kurz'     => 'Conditio humana bezeichnet die Grundbedingungen menschlicher Existenz: Körperlichkeit, Verletzlichkeit, Endlichkeit und Sterblichkeit. Sie sind keine Fehler des Menschseins, sondern seine Voraussetzung.',
			'content'  => [
				'Die conditio humana benennt das, wovor technische Erlösungsfantasien am liebsten fliehen: dass der Mensch geboren wird, leidet, liebt, altert und stirbt.',
				'Gerade diese Grenzen machen menschliches Leben verbindlich. Weil Zeit begrenzt ist, bekommen Entscheidungen Gewicht. Weil Körper verletzlich sind, entsteht Fürsorge. Weil Menschen sterblich sind, wird Erinnerung bedeutsam.',
				'Wer die conditio humana abschaffen will, riskiert, auch das Menschliche selbst zu verlieren. Nicht jede Grenze ist ein Gefängnis; manche Grenzen sind die Form, in der Sinn überhaupt entstehen kann.',
			],
			'synonyme' => [ 'conditio humana' ],
		],
		[
			'slug'     => 'determinismus-mechanistischer',
			'title'    => 'Determinismus (mechanistischer)',
			'kurz'     => 'Mechanistischer Determinismus versteht Welt, Leben und Geist als vollständig berechenbare Kausalkette. Er reduziert Wirklichkeit auf Maschine, Ablauf und Kontrolle.',
			'content'  => [
				'Der mechanistische Determinismus denkt das Universum wie ein Uhrwerk: Wenn alle Ausgangsdaten bekannt wären, ließe sich alles berechnen. Zufall, Freiheit und Bewusstsein erscheinen dann als bloße Illusion oder als noch nicht verstandene Mechanik.',
				'Dieses Weltbild hat enorme technische Macht freigesetzt. Gleichzeitig hat es eine Verengung produziert: Was nicht messbar, linear oder kausal eindeutig ist, wird schnell als irrational oder nebensächlich abgewertet.',
				'Für das Verständnis des Menschen ist diese Perspektive zu arm. Sie erklärt Abläufe, aber nicht Bedeutung; Reizverarbeitung, aber nicht Erfahrung; Bewegung, aber nicht gelebtes Leben.',
			],
			'synonyme' => [ 'mechanistischer Determinismus', 'mechanistischen Determinismus', 'berechenbares Uhrwerk' ],
		],
		[
			'slug'     => 'mind-uploading',
			'title'    => 'Mind Uploading',
			'kurz'     => 'Mind Uploading ist die hypothetische Idee, Bewusstsein digital zu kopieren oder zu übertragen. Sie setzt voraus, dass ein Mensch letztlich als Informationsmuster rekonstruierbar ist.',
			'content'  => [
				'Mind Uploading gehört zu den zentralen Erlösungsbildern des Transhumanismus. Der Körper stirbt, aber das Ich soll als Datensatz, Simulation oder digitale Kontinuität weiterbestehen.',
				'Das Versprechen hängt an einer gewaltigen Annahme: dass Bewusstsein vollständig aus Struktur, Information und Verarbeitung erklärbar ist. Doch selbst eine perfekte Kopie neuronaler Muster wäre nicht automatisch subjektives Erleben.',
				'Philosophisch bleibt deshalb die entscheidende Frage offen: Würde dort jemand weiterleben, oder entstünde nur ein digitales Abbild, das behauptet, jemand zu sein?',
			],
			'synonyme' => [ 'Mind-Uploading', 'Upload des Menschen', 'digitale Unsterblichkeit' ],
		],
		[
			'slug'     => 'enhancement-technologien',
			'title'    => 'Enhancement-Technologien',
			'kurz'     => 'Enhancement-Technologien sind Eingriffe, die nicht primär heilen, sondern menschliche Fähigkeiten über ein normales Maß hinaus steigern sollen.',
			'content'  => [
				'Enhancement beginnt dort, wo Medizin nicht mehr nur Leid lindert, sondern Leistungsfähigkeit, Wahrnehmung, Körper oder Kognition optimieren soll.',
				'Die Grenze zwischen Heilung und Steigerung ist nicht immer sauber. Eine Prothese kann verlorene Fähigkeit ersetzen, aber auch zur Fantasie eines überlegenen Körpers werden. Medikamente können Krankheit behandeln, aber auch Produktivität erzwingen.',
				'Politisch brisant wird Enhancement, sobald Optimierung zur sozialen Pflicht wird. Dann entscheidet nicht mehr nur die Technik, was möglich ist, sondern der Markt, wer mithalten muss.',
			],
			'synonyme' => [ 'Enhancement', 'Enhancements', 'technologische Erweiterungen', 'kognitive Erweiterungen' ],
		],
		[
			'slug'     => 'phanomenologie-des-leibes',
			'title'    => 'Phänomenologie des Leibes',
			'kurz'     => 'Die Phänomenologie des Leibes versteht den Körper nicht als Objekt, das der Mensch besitzt, sondern als Weise, in der er überhaupt Welt erfährt.',
			'content'  => [
				'Der Leib ist nicht bloß biologische Trägermasse. Er ist der Ort, von dem aus Welt erscheint: Nähe, Schmerz, Berührung, Müdigkeit, Orientierung und Angst sind keine abstrakten Daten, sondern leibliche Erfahrungen.',
				'Damit widerspricht die Phänomenologie des Leibes der alten Trennung von Geist und Körper. Bewusstsein schwebt nicht über dem Organismus, sondern vollzieht sich durch ihn.',
				'Für die Kritik digitaler Unsterblichkeitsfantasien ist das entscheidend. Ein entkörpertes Bewusstsein wäre nicht einfach derselbe Mensch ohne Fleisch, sondern ein radikal anderes Verhältnis zur Welt.',
			],
			'synonyme' => [ 'Phänomenologie des Leibs', 'leiblicher Vollzug' ],
		],
		[
			'slug'     => 'verkoerperte-kognition',
			'title'    => 'Verkörperte Kognition (Embodied Cognition)',
			'kurz'     => 'Verkörperte Kognition beschreibt Denken als leiblich eingebetteten Prozess. Geist entsteht nicht isoliert im Gehirn, sondern im Zusammenspiel von Körper, Umwelt und Handlung.',
			'content'  => [
				'Verkörperte Kognition bricht mit der Vorstellung, Denken sei reine Informationsverarbeitung im Kopf. Wahrnehmen, Erinnern und Entscheiden sind an Haltung, Bewegung, Nervensystem, Atmung, Raum und Situation gebunden.',
				'Der Mensch erkennt Welt nicht wie ein Computer, der Daten verarbeitet. Er greift, scheitert, tastet, spürt, wiederholt und lernt durch leibliche Rückkopplung.',
				'Diese Perspektive macht deutlich, warum Bewusstsein nicht beliebig vom Körper getrennt werden kann. Wer den Leib aus dem Denken herausrechnet, versteht Denken nur noch als blasse Simulation.',
			],
			'synonyme' => [ 'verkörperte Kognition', 'verkörperten Kognition', 'Embodied Cognition' ],
		],
		[
			'slug'     => 'hartes-problem-des-bewusstseins',
			'title'    => 'Hartes Problem des Bewusstseins',
			'kurz'     => 'Das harte Problem des Bewusstseins fragt, warum physikalische Prozesse überhaupt subjektives Erleben hervorbringen. Es markiert die Grenze zwischen Erklärung von Funktion und Erklärung von Erfahrung.',
			'content'  => [
				'Viele Vorgänge im Gehirn lassen sich funktional beschreiben: Reize werden verarbeitet, Informationen integriert, Verhalten gesteuert. Doch damit ist noch nicht erklärt, warum sich etwas innerlich anfühlt.',
				'Das harte Problem beginnt genau an dieser Stelle. Es fragt nicht nur, wie Wahrnehmung funktioniert, sondern warum es überhaupt ein Erleben gibt: Schmerz, Rot, Angst, Musik, Erinnerung, Ich-Gefühl.',
				'Für transhumanistische Modelle ist diese Lücke unbequem. Solange subjektives Erleben nicht verstanden ist, bleibt jede Behauptung einer digitalen Bewusstseinskopie metaphysisch überzogen.',
			],
			'synonyme' => [ 'harte Problem des Bewusstseins', 'hartes Problem', 'harte Problem', 'hard problem of consciousness' ],
		],
		[
			'slug'  => 'biophilie',
			'title' => 'Biophilie',
			'kurz'  => 'Biophilie bezeichnet die Zuneigung zum Lebendigen, Wachsenden und Unverfügbaren. Sie ist der Gegenbegriff zur Angst vor organischer Unordnung.',
			'content' => [
				'Biophilie meint nicht sentimentale Naturromantik. Sie beschreibt eine Grundhaltung, die Leben nicht zuerst kontrollieren, sondern verstehen, schützen und begleiten will.',
				'Das Lebendige ist nie vollständig verfügbar. Es wächst, altert, widersetzt sich, verbindet sich und stirbt. Biophilie akzeptiert diese Offenheit nicht als Schwäche, sondern als Würde des Lebens.',
				'Gegen technokratische Kontrollfantasien setzt Biophilie eine andere Ethik: Nicht alles Wertvolle muss optimiert werden. Manches muss bewahrt, gepflegt und in Ruhe gelassen werden.',
			],
		],
		// --- Geopolitik & Kurdische Geschichte ---
		[
			'slug'    => 'nordkurdistan',
			'title'   => 'Nordkurdistan',
			'kurz'    => 'Nordkurdistan (kurdisch: Bakurê Kurdistanê, kurz: Bakur) bezeichnet die mehrheitlich kurdisch besiedelten Gebiete im Osten und Südosten der heutigen Türkei — darunter die Provinzen Diyarbakır, Van, Hakkari, Şırnak, Mardin und weitere.',
			'content' => [
				'Nordkurdistan (kurdisch: Bakurê Kurdistanê, kurz: Bakur) bezeichnet die mehrheitlich kurdisch besiedelten Gebiete im Osten und Südosten der heutigen Türkei — darunter die Provinzen Diyarbakır, Van, Hakkari, Şırnak, Mardin und weitere.',
				'Die Bezeichnung ist ein politischer Akt: Sie verweigert die türkische Staatsgeografie als alleinigen Bezugsrahmen und setzt eine kurdische Selbstverortung dagegen. Der türkische Staat bezeichnet dieselbe Region als „Südosten der Türkei".',
				'Weltweit leben schätzungsweise 30 bis 45 Millionen Kurdinnen und Kurden — verlässliche Zahlen existieren nicht, da die betroffenen Staaten ethnische Zugehörigkeit nicht oder nur unvollständig erfassen und höhere Zahlen Autonomieansprüche stärken würden. Damit sind die Kurden die größte ethnische Gruppe ohne eigenen Staat. Das Siedlungsgebiet verteilt sich auf vier Staaten: Türkei (Bakur/Nord), Irak (Başûr/Süd), Iran (Rojhilat/Ost), Syrien (Rojava/West). Die heutige Aufteilung geht auf den Vertrag von Lausanne (1923) zurück. In Deutschland lebt mit rund 1,2 bis 1,5 Millionen Menschen eine der größten kurdischen Diaspora-Gemeinschaften weltweit.',
			],
			'synonyme' => [ 'Bakur', 'Bakurê Kurdistanê', 'Nordkurdistans' ],
			'quellen'  => 'Kurdish Institute of Paris, Washington Kurdish Institute, CIA World Factbook, CNN (2026).',
			'topics'   => [ 'erinnerung-und-identitaet' ],
		],
		[
			'slug'    => 'sympatheia',
			'title'   => 'Sympatheia',
			'kurz'    => 'Stoischer Begriff für die Vorstellung, dass der gesamte Kosmos ein zusammenhängendes Ganzes bildet, dessen Teile aufeinander einwirken und miteinander schwingen.',
			'content' => [
				'Sympatheia (griechisch: συμπάθεια, „Mitempfinden") ist ein zentraler Begriff der stoischen Philosophie. Er bezeichnet die Vorstellung, dass der gesamte Kosmos ein zusammenhängendes, lebendiges Ganzes bildet, dessen Teile aufeinander einwirken und miteinander schwingen.',
				'Für die Stoiker — etwa Chrysipp, Poseidonios und später Mark Aurel — war Sympatheia nicht emotional, sondern ontologisch gemeint: Alles, was existiert, steht in wechselseitiger Beziehung. Eine Veränderung an einem Punkt wirkt auf das Ganze. Der Mensch ist darin kein Beobachter von außen, sondern Teil eines durchgehenden Gewebes.',
				'Der Begriff wirkt bis in die Moderne nach — in der Naturphilosophie, im ökologischen Denken und in Konzepten von Verbundenheit, die über das rein Zwischenmenschliche hinausgehen.',
			],
			'synonyme' => [ 'Sympatheia', 'συμπάθεια', 'stoische Sympatheia' ],
			'topics'   => [ 'sprache-und-begriff', 'gesellschaft-und-wandel' ],
		],
		// --- Transhumanismus-Komplex (erweitert) ---
		[
			'slug'    => 'terror-management-theorie',
			'title'   => 'Terror-Management-Theorie',
			'kurz'    => 'Psychologische Theorie (Ernest Becker), die postuliert, dass das Bewusstsein der eigenen Sterblichkeit eine fundamentale, unbewusste Angst erzeugt — und dass kulturelle Weltbilder, religiöse wie säkulare, primär als psychologischer Schutzschild gegen diesen Todesschrecken fungieren.',
			'content' => [
				'Die Terror-Management-Theorie (TMT) geht auf den Kulturanthropologen Ernest Becker und sein Werk „The Denial of Death" (1973) zurück. Sie postuliert: Die evolutionäre Entstehung des menschlichen Selbstbewusstseins kollidiert unweigerlich mit der Erkenntnis der eigenen Sterblichkeit. Das Ergebnis ist eine fundamentale, oft unbewusste Todesangst.',
				'Zur Abwehr dieses lähmenden Terrors konstruiert der Mensch kulturelle Weltbilder — Systeme aus Werten, Bedeutungen und Überzeugungen, die Sinn, Ordnung und persönlichen Wert vermitteln. Wörtliche Unsterblichkeitskonzepte (religiöse Jenseitsversprechen) und symbolische (Weiterleben im kollektiven Gedächtnis durch Kunst, Nachkommen, Nation) erfüllen dieselbe psychologische Funktion.',
				'Im Kontext des Transhumanismus ist TMT analytisch produktiv: Mit fortschreitender Säkularisierung verlieren metaphysische Unsterblichkeitserzählungen ihre Schutzwirkung. Der Transhumanismus füllt dieses Vakuum — er verlagert die wörtliche Unsterblichkeit aus dem transzendenten Jenseits in das immanente Diesseits der technologischen Machbarkeit. Kryokonservierung avanciert zur säkularen Auferstehungstechnologie; Mind Uploading zur technologischen Replikation der unsterblichen Seele.',
			],
			'synonyme' => [ 'TMT', 'Terror Management Theory', 'Mortalitätssalienz', 'Todesbewusstsein' ],
			'quellen'  => 'Becker, E. (1973). The Denial of Death. Free Press. — Greenberg, J., Pyszczynski, T. & Solomon, S. (1986). The causes and consequences of a need for self-esteem. In Public self and private self, 189–212.',
			'topics'   => [ 'gesellschaft-und-wandel', 'sprache-und-begriff' ],
		],
		[
			'slug'    => 'posthumanismus',
			'title'   => 'Posthumanismus',
			'kurz'    => 'Sammelbegriff für philosophische und kulturelle Positionen, die den gegenwärtigen Menschen nicht als finalen Endzustand, sondern als Übergangsform zu einem fundamental transformierten — biologisch, kognitiv oder digital anderen — Wesen verstehen.',
			'content' => [
				'Als Begriff ist Posthumanismus weiter als Transhumanismus: Er umfasst sowohl techno-optimistische Visionen einer verbesserten Spezies (Transhumanismus) als auch kritische Theorien, die den humanistischen Sonderstatus des Menschen grundsätzlich befragen — etwa aus feministischer, ökologischer oder postkolonialer Perspektive.',
				'In seiner affirmativ-transhumanistischen Variante beschreibt Posthumanismus den Zielzustand jenseits des biologisch determinierten Menschen: kognitiv erweitert, körperlich optimiert oder digital fortlebend. Nick Bostrom versteht den Transhumanen als Übergangsstadium auf dem Weg zum Posthumanen — einem Wesen mit so grundlegend veränderten Fähigkeiten, dass es nach heutigen Maßstäben nicht mehr als Mensch zu bezeichnen wäre.',
				'Kritische Posthumanisten (Donna Haraway, Rosi Braidotti) wenden die Perspektive um: Nicht Verbesserung, sondern Dekonstruktion der Ausnahmestellung des Menschen ist ihr Anliegen. Sie betonen Verflechtung, Hybridität und die Relativierung menschlicher Grenzen — ohne das Silicon-Valley-Erlösungsversprechen zu teilen.',
			],
			'synonyme' => [ 'posthuman', 'post-human', 'Post-Humanismus', 'Posthumane' ],
			'topics'   => [ 'gesellschaft-und-wandel', 'sprache-und-begriff' ],
		],
		[
			'slug'    => 'technologische-singularitaet',
			'title'   => 'Technologische Singularität',
			'kurz'    => 'Hypothetischer Zeitpunkt, ab dem künstliche Intelligenz die menschliche übersteigt und eine exponentielle, für Menschen unvorhersehbare technologische Selbstbeschleunigung einsetzt. Ray Kurzweil prognostizierte diesen Punkt auf das Jahr 2045.',
			'content' => [
				'Der Begriff Singularität — aus Mathematik und Physik entlehnt, wo er Punkte bezeichnet, an denen Gleichungen divergieren — wurde von Vernor Vinge (1993) auf technologische Entwicklung übertragen. Die Kernidee: Sobald Maschinen intelligenter als Menschen werden, entsteht eine selbstverstärkende Schleife aus KI-Verbesserung, die menschlichem Verstehen entzogen ist.',
				'Ray Kurzweil popularisierte die Singularität in „The Singularity Is Near" (2005) und datierte sie auf 2045. Bis dahin, so seine These, würden sich biologische und künstliche Intelligenz untrennbar verbinden. Kurzweil versteht die Singularität als Kulminationspunkt transhumanistischer Entwicklung: Tod, Krankheit und biologische Beschränkungen erscheinen als lösbare Ingenieursprobleme.',
				'Kritiker wenden ein, dass die Singularität auf unkritischer Extrapolation exponentieller Wachstumskurven beruht — und dass das harte Problem des Bewusstseins zeigt, warum bloße Rechenleistung kein Äquivalent zu menschlicher Erfahrung oder Kreativität ist. Die Singularität ist weniger Prognose als Erlösungsnarrativ in der Sprache der Informatik.',
			],
			'synonyme' => [ 'Singularität', 'Technological Singularity', 'KI-Singularität', 'technologische Konvergenz' ],
			'quellen'  => 'Kurzweil, R. (2005). The Singularity Is Near. Viking. — Vinge, V. (1993). The Coming Technological Singularity. VISION-21 Symposium.',
			'topics'   => [ 'gesellschaft-und-wandel', 'macht-und-ordnung' ],
		],
		[
			'slug'    => 'longtermismus',
			'title'   => 'Longtermismus',
			'kurz'    => 'Philosophische Haltung aus dem Effective-Altruism-Umfeld, die das langfristige Wohlergehen zukünftiger Generationen als primäre moralische Priorität der Gegenwart setzt — und daraus die Fokussierung auf existenzielle Risiken (besonders KI) ableitet.',
			'content' => [
				'Longtermismus geht davon aus, dass die Zukunft der Menschheit astronomisch lang und potenziell astronomisch gut sein könnte — vorausgesetzt, wir vermeiden existenzielle Katastrophen. Da die Anzahl zukünftiger Menschen alle bisher Geborenen übersteigt, folgt für Longtermisten: Die Verbesserung langfristiger Zukunftsperspektiven hat moralisch das größte Gewicht.',
				'Theoretisch verankert bei William MacAskill und Toby Ord (Effective Altruism), institutionell im Future of Humanity Institute (FHI, Oxford, 2005–2024) und im Future of Life Institute. Das FHI — mitfinanziert von Elon Musk und Open Philanthropy — entwickelte Konzepte wie KI-Alignment, existenzielle Risikoforschung und die „vulnerable Welt"-Hypothese. Es wurde im April 2024 von der Universität Oxford geschlossen, was Beobachter als Ende der akademischen Phase und Beginn eines dezentralen, privatwirtschaftlichen Ökosystems interpretierten.',
				'Kritiker werfen dem Longtermismus vor, gegenwärtiges Leid zu entwerten, eine techno-elitäre Agenda zu verfolgen und durch seinen universalistischen Anspruch reale Machtasymmetrien zu verschleiern. Die enge Verbindung zu Silicon-Valley-Milliardären und KI-Unternehmen macht die politische Neutralität des Projekts fraglich.',
			],
			'synonyme' => [ 'Longtermism', 'Langzeitorientierung', 'Existenzrisiko-Forschung', 'Effective Altruism' ],
			'quellen'  => 'MacAskill, W. (2022). What We Owe the Future. Basic Books. — Future of Humanity Institute (2005–2024), Universität Oxford.',
			'topics'   => [ 'gesellschaft-und-wandel', 'macht-und-ordnung' ],
		],
		[
			'slug'     => 'pessimismus-philosophischer',
			'title'    => 'Pessimismus (philosophischer)',
			'kurz'     => 'Philosophischer Pessimismus versteht Leiden nicht als Ausnahme, sondern als Grundzug des Daseins. Seine Stärke liegt in der Nüchternheit, seine Gefahr in der Verwechslung von Geschichte und Metaphysik.',
			'content'  => [
				'Philosophischer Pessimismus nimmt Schmerz, Verlust und Enttäuschung ernst. Er weigert sich, menschliches Leiden mit Fortschrittsparolen oder moralischem Kitsch zu überdecken.',
				'Doch seine Schärfe kann in eine Falle führen. Wenn alles Leiden als metaphysische Grundsignatur des Lebens erscheint, verschwinden konkrete Ursachen: Ausbeutung, Armut, Patriarchat, Krieg, Entwurzelung und politische Gewalt.',
				'Eine brauchbare Kritik muss deshalb unterscheiden. Nicht jedes Leid ist abschaffbar, aber vieles ist gemacht. Wer diesen Unterschied verwischt, verwandelt historische Verhältnisse in Schicksal.',
			],
			'synonyme' => [ 'Pessimismus', 'philosophischer Pessimismus' ],
		],
	];

	$changed = false;

	foreach ( $entries as $entry ) {
		$existing = get_page_by_path( $entry['slug'], OBJECT, 'glossar' );
		if ( $existing instanceof WP_Post ) {
			$changed = hp_update_seeded_glossar_post( $existing, $entry ) || $changed;
			$changed = hp_update_seeded_glossar_meta( $existing->ID, $entry ) || $changed;
			hp_seed_glossar_topics( $existing->ID, $entry );
			continue;
		}

		$post_id = wp_insert_post( [
			'post_type'    => 'glossar',
			'post_status'  => 'publish',
			'post_name'    => $entry['slug'],
			'post_title'   => $entry['title'],
			'post_excerpt' => $entry['kurz'],
			'post_content' => hp_build_glossar_seed_content( $entry ),
		], true );

		if ( is_wp_error( $post_id ) || ! $post_id ) {
			continue;
		}

		$changed = hp_update_seeded_glossar_meta( (int) $post_id, $entry ) || $changed;
		hp_seed_glossar_topics( (int) $post_id, $entry );
	}

	if ( $changed ) {
		hp_glossar_seed_bump_cache_version();
	}
}

/**
 * Aktualisiert redaktionelle Seed-Postfelder für bestehende Glossar-Begriffe.
 *
 * @param WP_Post              $post  Bestehender Glossar-Post.
 * @param array<string, mixed> $entry Seed-Definition.
 * @return bool Ob sich ein Wert geändert hat.
 */
function hp_update_seeded_glossar_post( WP_Post $post, array $entry ): bool {
	$post_data = [
		'ID' => $post->ID,
	];

	if ( isset( $entry['title'] ) && (string) $entry['title'] !== (string) $post->post_title ) {
		$post_data['post_title'] = (string) $entry['title'];
	}

	if ( isset( $entry['kurz'] ) && (string) $entry['kurz'] !== (string) $post->post_excerpt ) {
		$post_data['post_excerpt'] = (string) $entry['kurz'];
	}

	$content = hp_build_glossar_seed_content( $entry );
	if ( '' !== $content && $content !== (string) $post->post_content ) {
		$post_data['post_content'] = $content;
	}

	if ( 1 === count( $post_data ) ) {
		return false;
	}

	$updated = wp_update_post( $post_data, true );

	return ! is_wp_error( $updated ) && (bool) $updated;
}

/**
 * Baut den Glossar-Body als Gutenberg-kompatible Absatzblöcke.
 *
 * @param array<string, mixed> $entry Seed-Definition.
 * @return string Gutenberg-Block-Content.
 */
function hp_build_glossar_seed_content( array $entry ): string {
	$paragraphs = [];

	if ( ! empty( $entry['content'] ) && is_array( $entry['content'] ) ) {
		$paragraphs = array_map( 'strval', $entry['content'] );
	} elseif ( ! empty( $entry['content'] ) && is_string( $entry['content'] ) ) {
		$split = preg_split( '/\R{2,}/', $entry['content'] );
		if ( is_array( $split ) ) {
			$paragraphs = $split;
		}
	}

	if ( empty( $paragraphs ) && isset( $entry['kurz'] ) ) {
		$paragraphs = [ (string) $entry['kurz'] ];
	}

	$blocks = [];
	foreach ( $paragraphs as $paragraph ) {
		$paragraph = trim( (string) $paragraph );
		if ( '' === $paragraph ) {
			continue;
		}

		$blocks[] = "<!-- wp:paragraph -->\n<p>" . esc_html( $paragraph ) . "</p>\n<!-- /wp:paragraph -->";
	}

	return implode( "\n\n", $blocks );
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

	if ( array_key_exists( 'quellen', $entry ) ) {
		$quellen = is_array( $entry['quellen'] )
			? implode( "\n", array_map( 'strval', $entry['quellen'] ) )
			: (string) $entry['quellen'];
		$changed = update_post_meta( $post_id, '_hp_glossar_quellen', $quellen ) || $changed;
	}

	if ( array_key_exists( 'version', $entry ) ) {
		$changed = update_post_meta( $post_id, '_hp_glossar_version', (string) $entry['version'] ) || $changed;
	}

	if ( array_key_exists( 'stand', $entry ) ) {
		$changed = update_post_meta( $post_id, '_hp_glossar_stand', (string) $entry['stand'] ) || $changed;
	}

	return $changed;
}

/**
 * Setzt Themenfelder (Taxonomy „topic") für einen Glossar-Eintrag.
 *
 * @param int                  $post_id Glossar-Post-ID.
 * @param array<string, mixed> $entry   Seed-Definition.
 */
function hp_seed_glossar_topics( int $post_id, array $entry ): void {
	if ( empty( $entry['topics'] ) || ! is_array( $entry['topics'] ) ) {
		return;
	}

	if ( ! taxonomy_exists( 'topic' ) ) {
		return;
	}

	wp_set_object_terms( $post_id, $entry['topics'], 'topic', false );
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
	$content_version = 'r7-sterblichkeit-korrekturen';
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
			'<p>Im Silicon Valley wird dieses Faktum anders verhandelt. Dort gilt Altern nicht als Bedingung der Existenz, sondern als Konstruktionsfehler — als „Akkumulation zellulärer Schäden", behebbar wie fehlerhafter Code. Das ist kein Zerrbild, sondern erklärtes Programm, unterlegt mit enormem Kapital: Altos Labs startete 2022 mit drei Milliarden Dollar, gestützt von Jeff Bezos — der bestfinanzierte Biotech-Start der Geschichte. Retro Biosciences, finanziert vom OpenAI-Chef Sam Altman, sammelte zuletzt Kapital zu einer Bewertung von 1,8 Milliarden Dollar ein, mit dem erklärten Ziel, dem menschlichen Leben zehn gesunde Jahre hinzuzufügen. Ray Kurzweil terminiert das Verschmelzen von Mensch und Maschine auf das Jahr 2045.</p>',
			'<p>Bevor man das verwirft, muss man den stärksten Einwand der Gegenseite gelten lassen: Altern ist die größte Einzelursache menschlichen Leidens; täglich sterben weltweit über hunderttausend Menschen an altersbedingten Ursachen. Wenn sich dieser Prozess medizinisch verlangsamen lässt — wäre es nicht zynisch, es nicht zu versuchen? Das Argument ist ernst zu nehmen. Es ist gut, Leiden zu lindern, Krankheiten zu behandeln, verlorene Funktionen wiederherzustellen — das bestreite ich nicht. Mein Einwand setzt woanders an.</p>',
			'<p>Der Bruch liegt nicht hier. Er liegt dort, wo aus Medizin eine Metaphysik wird — wo Heilung nicht mehr heilen will, sondern erlösen. Wo der Mensch nicht mehr als verletzliches, sterbliches Wesen gilt, sondern als defektes System, das auf sein Update wartet. Der Transhumanismus wird nicht durch seine Werkzeuge fragwürdig, sondern durch das Menschenbild, das er mitliefert.</p>',
		],
		'II. Der Kategorienfehler: Wenn der Geist das Fleisch verlässt' => [
			'<p>Der transhumanistische Traum vom „Mind Uploading" — der Übertragung des Bewusstseins auf einen Datenträger — lebt von einem alten philosophischen Irrtum. René Descartes spaltete die Welt im 17. Jahrhundert in eine denkende Substanz und eine ausgedehnte, materielle Welt. Der Körper wurde zur Maschine, gesteuert von einem unkörperlichen Geist. Das Silicon Valley hat diesen Dualismus lediglich digitalisiert: Das Gehirn gilt als Prozessor, der Körper als austauschbare Peripherie, das Selbst als Information, die zufällig auf biologischer Hardware läuft.</p>',
			'<p>Gegen dieses Bild steht ein gut belegter Einwand. Die Phänomenologie des Leibes, wie sie Maurice Merleau-Ponty entwickelt hat, zeigt: Der Mensch <em>hat</em> seinen Körper nicht, er <em>ist</em> sein Körper. Wahrnehmung, Absicht, Orientierung in der Welt sind keine Rechenleistung, die im Schädel stattfindet — sie sind leiblich. Ich bin kein Pilot in einem Fahrzeug. Das Selbst entsteht im Vollzug: in der Bewegung, im Tasten, im Stoffwechsel, im Zusammenspiel von Hormonen, im Mikrobiom des Darms, in der ständigen, größtenteils unbewussten Rückkopplung zwischen Körper und Umwelt. Die kognitionswissenschaftliche Forschung zur „verkörperten Kognition" hat diese Einsicht in den letzten Jahrzehnten breit untermauert: Denken ist kein körperloser Prozess.</p>',
			'<p>Und dieser Körper ist kein dummes Trägermaterial. Schneide dir in den Finger — und ohne dass du etwas tust, ohne dass du auch nur weißt, wie es geht, gerinnt das Blut, wandern Zellen ein, schließt sich die Wunde. Keine von Menschen gebaute Maschine kann das. Das Immunsystem unterscheidet unzählige Bedrohungen und erinnert sich über Jahrzehnte an sie; Hormone, Nerven und Organe stehen in einer Rückkopplung, die kein Ingenieur entworfen hat und niemand vollständig versteht. Der Körper ist in diesem Sinne intelligent — nicht weil er rechnet, sondern weil er sich organisiert, heilt und im Gleichgewicht hält. Wer das sieht, dem erscheint die Rede vom „biologischen Altbau" nicht nur falsch, sondern blind.</p>',
			'<p>Es wäre nun verlockend, dem transhumanistischen Bild vom Körper als Schrott einfach das Gegenbild entgegenzuhalten: den Körper als perfekte Konstruktion. Auch das wäre ein Fehler — nicht weil der Körper schlecht wäre, sondern weil „perfekt" der falsche Maßstab ist. Perfektion ist ein Ingenieursbegriff: reibungslos, optimal, fehlerfrei. Ein Organismus ist nichts davon, und er muss es nicht sein. Die vielzitierten „Konstruktionsfehler" — der blinde Fleck im Auge, der gekreuzte Weg von Atem und Nahrung, die Wirbelsäule, die den aufrechten Gang teuer bezahlt — sind keine Pfuscharbeit. Es sind Kompromisse, und Kompromisse sind die Signatur eines Anpassungsprozesses, der unter realen Bedingungen arbeitet, nicht am Reißbrett. Gemessen an der einzigen Aufgabe, die ein lebendiger Körper hat — zu leben, zu wachsen, sich zu erhalten, sich zu reparieren, eine Lebensspanne lang —, ist er erstaunlich gut. Wir sind nicht annähernd so mangelhaft, wie die Erlösungserzählung es braucht.</p>',
			'<p>Der entscheidende Punkt bleibt: Wer den Körper gegen den Vorwurf des Konstruktionsfehlers verteidigt, indem er ihn perfekt nennt, hat die Prämisse der Transhumanisten schon übernommen — den Körper überhaupt als Konstruktion zu beurteilen, als gelungene oder misslungene Maschine. Der Körper ist keine Maschine, weder eine gute noch eine schlechte. Er ist ein lebendiger Prozess. Seine Intelligenz und seine Sterblichkeit sind nicht zwei Eigenschaften, sondern eine einzige: Was sich selbst organisiert, wächst und anpasst, ist eben dadurch an Stoffwechsel, Reibung, Verschleiß und Endlichkeit gebunden. Eine Maschine lässt sich im Prinzip endlos reparieren, weil sie nur aus Teilen besteht. Ein Organismus nicht — weil er lebt.</p>',
			'<p>Hier ist Vorsicht geboten — in zwei Richtungen. Es wäre dogmatisch zu behaupten, Bewusstsein könne unter keinen Umständen ohne Biologie existieren. Das Phänomen des Bewusstseins ist ungelöst. Der Philosoph David Chalmers nennt es das „harte Problem": Niemand kann erklären, warum Informationsverarbeitung überhaupt von subjektivem Erleben begleitet wird. Ob es nicht-biologische Formen von Bewusstsein geben kann, weiß niemand. Aber genau diese Offenheit ist der Punkt. Der Transhumanismus behandelt die Frage nicht als offen — er verkauft eine Antwort. Er behauptet nicht nur, irgendein Bewusstsein sei denkbar, sondern: dein konkretes Ich, deine Erinnerungen, deine Person ließen sich kopieren und fortsetzen. Das ist kein mutiger Blick in die Wissenschaft. Das ist eine Spekulation im Tonfall der Ingenieurskunst.</p>',
			'<p>Und selbst wenn die Technik eines Tages gelänge, bliebe ein Einwand, der sich an einem Gedankenexperiment des Philosophen Derek Parfit (1984) schärfen lässt. Man stelle sich ein Gerät vor, das einen Menschen scannt, das Original zerstört und anderswo eine atomgenaue Kopie erzeugt. Die Kopie erinnert sich an alles, hält sich für dieselbe Person. Aber bliebe das Original am Leben, stünden sich zwei Menschen gegenüber, nicht einer. Ähnlichkeit ist keine Identität. Eine perfekte digitale Kopie meiner Person wäre ein beeindruckendes Archiv — ein interaktives Denkmal, eine Täuschung für die Hinterbliebenen. Sie wäre nicht die Fortsetzung meines erlebten Bewusstseins. Wer das verspricht, überwindet den Tod nicht. Er kaschiert ihn. Digitale Unsterblichkeit ist keine Auferstehung, sondern Nachlassverwaltung mit Benutzeroberfläche.</p>',
			'<p>Dass diese Skepsis nicht nur philosophisch, sondern auch naturwissenschaftlich begründet ist, zeigt ein nüchterner Blick auf die Forschung. Seit über einem Jahrzehnt versucht das internationale Projekt OpenWorm, das Nervensystem des Fadenwurms <em>Caenorhabditis elegans</em> vollständig digital nachzubilden — einen der einfachsten Organismen überhaupt, mit exakt 302 Neuronen, jede Verbindung kartiert. Es ist bis heute nicht gelungen, diesen Wurm so zu emulieren, dass er sich verhält wie sein lebendiges Vorbild. 302 Neuronen. Das menschliche Gehirn hat rund 86 Milliarden. Das beweist nicht, dass ein Upload prinzipiell unmöglich ist — aber wer nicht einmal einen Wurm emulieren kann, sollte den Upload des Menschen nicht als absehbar verkaufen.</p>',
			'<p>Es gibt einen weiteren Verlust, den das technokratische Denken übersieht: die Bedeutung der Form. Die materielle Welt ist für den Menschen kein neutraler Trägerstoff. Unsere Kreativität, unsere Kunst, unser Denken entzünden sich am Widerstand und an der Gestalt der physischen Welt — an der Maserung des Holzes, der Geometrie des Wachsenden, dem Gewicht des Steins. Ein Bewusstsein in der Cloud verlöre nicht nur seine biologische Resonanz, das Klopfen des Herzens bei Angst, das Zusammenspiel von Berührung und Bindung. Es verlöre den schöpferischen Dialog mit der Materie selbst. Es wäre kein befreiter Geist, sondern ein Selbstgespräch in sensorischer Isolation.</p>',
		],
		'III. Die Angst vor dem Unverfügbaren' => [
			'<p>Warum hält sich eine so fragile Utopie ausgerechnet bei den einflussreichsten Menschen des Planeten? Die Antwort liegt weniger in der Wissenschaft als in einer kulturellen Disposition.</p>',
			'<p>Erich Fromm hat der Biophilie — der Zuneigung zum Lebendigen, Wachsenden, Unberechenbaren — die Nekrophilie gegenübergestellt: die Neigung zum Mechanischen, Toten, restlos Kontrollierbaren. Man muss daraus keine Ferndiagnose einzelner Personen machen, um den kulturellen Sog zu erkennen. Er hat einen Kern: die Unfähigkeit, das Unverfügbare auszuhalten — all das, was sich grundsätzlich nicht herstellen, steuern oder optimieren lässt. Das Lebendige zeichnet sich gerade dadurch aus, dass es sich der vollständigen Kontrolle entzieht. Es altert, erkrankt, stirbt, lässt sich nicht in Metriken pressen.</p>',
			'<p>Für eine Kultur, die gelernt hat, dass sich jedes Problem mit dem richtigen Algorithmus, genug Rechenleistung und genug Kapital lösen lässt, ist diese Unverfügbarkeit eine Kränkung. Die Flucht ins Verjüngungslabor und in die digitale Unsterblichkeit ist der Versuch, das Lebendige so lange in Daten zu übersetzen, bis es endlich berechenbar ist.</p>',
			'<p>Der Transhumanismus hört an einem Punkt auf zu fragen, was Technik dem Menschen ermöglichen kann, und beginnt zu behaupten, wohin der Mensch sich zu entwickeln habe. An diesem Punkt wird aus einer Forschungsagenda eine Weltanschauung — eine, die das Organische zum Provisorium erklärt und das Mechanische zur Verheißung. Sie ist deshalb nicht automatisch falsch. Aber sie ist unehrlich, wenn sie sich als bloße Wissenschaft ausgibt. Wissenschaft beschreibt, prüft, verwirft. Eine Heilsbotschaft verspricht eine Richtung der Geschichte.</p>',
		],
		'IV. Die soziale Demaskierung' => [
			'<p>Holt man das transhumanistische Projekt aus der philosophischen Höhe auf den Boden der Gesellschaft, verliert es seine humanitäre Maske vollends.</p>',
			'<p>Lebenszeit ist schon heute ungleich verteilt. Eine vielzitierte Studie des Ökonomen Raj Chetty, 2016 im <em>Journal of the American Medical Association</em> veröffentlicht und auf 1,4 Milliarden Steuerdatensätzen beruhend, zeigt: Zwischen dem reichsten und dem ärmsten Prozent der US-Bevölkerung liegt eine Lücke in der Lebenserwartung von etwa fünfzehn Jahren bei Männern und zehn bei Frauen. Aber — und das ist entscheidend — diese Lücke ist kein biologisches Rätsel, das nach Gen-Scheren und Longevitäts-Pillen verlangt. Sie ist das Ergebnis ungleicher Lebensbedingungen: chronischer Stress, schlechtere Ernährung, härtere Arbeit, weniger Schlaf, weniger Sicherheit. Sie schließt sich nicht durch Technologie, sondern durch Verteilung.</p>',
			'<p>Das ist der eigentliche Befund. Die wirksamsten Mittel für ein langes, gesundes Leben sind längst bekannt und zutiefst unspektakulär: Sicherheit, Ruhe, gute Arbeit, soziale Bindung, das Gefühl, nicht permanent ausgenutzt zu werden. Eine gerechtere Gesellschaft verlängert das Leben vieler Menschen — ganz ohne Silizium. Eine marktförmig organisierte Lebensverlängerung dagegen verteilt Lebenszeit nicht um; sie legt sich auf ein bestehendes Gefälle und macht es steiler. Und der Reiche, der sich mit Transfusionen, Gentherapien und Pillen versorgt, wird dadurch nicht zu einem höheren Menschen. Er wird zu einem hyper-medizinisierten Exponat. Seine Existenz wird nicht tiefer, sondern steriler.</p>',
			'<p>Hinter dem Drang nach technischer Unsterblichkeit steht zudem das Symptom einer erschöpften Kultur. Wohlstand allein macht nicht glücklich: Die USA, eines der reichsten Länder der Erde, sind im World Happiness Report 2025 auf den 24. Platz gefallen — den niedrigsten Wert seit Beginn der Erhebung. Soziologen sprechen von „deaths of despair", von wachsender Vereinsamung, von einer Zunahme psychischer Erkrankungen mitten im materiellen Überfluss. Der Mensch funktioniert in einem hyperkompetitiven System aus Konsumdruck und permanenter Konkurrenz nicht mehr richtig — und statt die krankmachenden Strukturen zu reparieren, bietet das Silicon Valley die Flucht in die künstliche Ewigkeit an.</p>',
			'<p>Hier liegt ein tieferer Trugschluss. Die transhumanistische Flucht setzt voraus, dass das Leben selbst — verkörpert, endlich, sterblich — so mangelhaft sei, dass der Ausstieg die vernünftige Antwort wäre. Doch hier werden zwei Dinge verwechselt, die nichts miteinander zu tun haben. Das eine ist die Endlichkeit: dass wir altern und sterben. Sie ist kein Leiden, sondern die Bedingung des Lebendigen — ob sie als Bedrohung erscheint oder nicht, hängt vom Verhältnis zu ihr ab, nicht von ihr selbst. Das andere ist das wirkliche Unbehagen der Gegenwart: die Erschöpfung, die Vereinsamung, das Gefühl, verbraucht zu werden. Das ist real — aber es ist nicht die metaphysische Signatur des Menschseins, sondern das Ergebnis historisch gemachter Macht- und Wirtschaftsverhältnisse. Der Transhumanismus liest die Endlichkeit als Leid und nimmt das gemachte Unbehagen zum Beweis, dass das endliche Leben selbst defekt sei. Damit begeht er einen zweiten Kategorienfehler: Nachdem er den Menschen mit einer Maschine verwechselt hat, verwechselt er nun ein politisches Problem mit einem existenziellen. Das ist folgenreich — denn ein krankes System lässt sich ändern, eine angeblich kranke Existenz nicht. Wer erkennt, dass das Unbehagen gemacht wurde, kann es auch ungemacht denken. Eine bewohnbare Welt ist keine naive Hoffnung, sondern eine politische Möglichkeit — und gerade sie verstellt der Fluchtgedanke.</p>',
			'<p>Diese Flucht entlastet. Wer glaubt, bald auf einem Server fortzuleben, muss sich um den Verfall des Sozialstaats, die Einsamkeit in den Pflegeheimen, die Würde des Sterbens weniger kümmern. Die Obszönität des Transhumanismus liegt darin, dass er das Falsche zuerst will: die Verlängerung des Lebens einiger weniger, bevor die Lebensbedingungen der vielen gesichert sind.</p>',
		],
		'V. Der Gegenentwurf: Sorge und Endlichkeit' => [
			'<p>Der Gegenentwurf ist keine Technikfeindlichkeit. Wir müssen nicht kleiner von der Technik denken, sondern größer vom Menschen. Fortschritt bemisst sich dann nicht daran, wie weit wir uns von unserer biologischen Natur entfernen, sondern wie tief wir ihr gerecht werden. Drei Richtungen, konkret.</p>',
			'<p><strong>Erstens: die Aufwertung der Sorge.</strong> Ein System, das Milliarden für die Abschaffung des Todes mobilisiert, aber Pflegekräfte am Mindestlohn hält, ist moralisch in Schieflage. Die Antwort auf Verletzlichkeit ist nicht ihre Abschaffung, sondern Zuwendung — Pflege, Erziehung, Palliativmedizin gehören ins Zentrum der Gesellschaft, nicht an ihren Rand. Eine Zivilisation zeigt ihren Rang nicht daran, wie alt ihre Milliardäre werden, sondern daran, wie sie mit ihren Schwächsten umgeht.</p>',
			'<p><strong>Zweitens: die Anerkennung der Endlichkeit.</strong> Der Philosoph Bernard Williams hat 1973 in seinem Essay über den „Fall Makropulos" gezeigt, warum ein unendliches Leben nicht erstrebenswert, sondern unerträglich wäre: Was unsere Wünsche, Bindungen und Entscheidungen mit Bedeutung auflädt, ist ihre Verknüpfung mit einem endlichen Leben. Unendlichkeit ist Stillstand. Erst weil Zeit begrenzt ist, hat sie Gewicht; erst weil wir verschwinden, ist es nicht gleichgültig, wie wir leben. Eine Palliativmedizin, die Schmerz lindert und ein würdiges Sterben in Gemeinschaft ermöglicht, ist humaner als die Verlängerung des bloßen biologischen Funktionierens um jeden Preis.</p>',
			'<p><strong>Drittens: der Schutz der analogen Lebenswelt.</strong> Der Philosoph Yuk Hui erinnert mit dem Begriff der „Kosmotechnik" daran, dass Technik immer in eine Ordnung eingebettet ist — in Beziehung zu Körper, Ort, Gemeinschaft und Natur. Eine humane technologische Kultur baut Städte, Schulen und Räume, in denen der Körper Heimat findet, statt den Menschen in die sensorische Verarmung der Bildschirme zu treiben. Sie fragt nicht nur, was machbar ist, sondern in welche Ordnung das Machbare gehört.</p>',
			'<p>Der Mensch ist kein defektes Gerät. Der Körper ist kein Gefängnis. Bewusstsein ist keine Datei. Sterblichkeit ist kein Softwarefehler — sie ist das Gesetz des Lebendigen.</p>',
			'<p>Eine Maschine ist ein fertiges Produkt. Ein Mensch ist es nie. Wir sind das Ergebnis von fast vier Milliarden Jahren ununterbrochenen Werdens — und auch das einzelne Leben bleibt ein Werden, bis zuletzt. Ein Neugeborenes ist keine leere Festplatte: Es trägt ein tiefes biologisches Erbe in sich, die Anlage zur Sprache, zum aufrechten Gang, zur Zuwendung. Aber es ist ebenso wenig ein fertiges Programm. Es entfaltet sich erst — lernt, wächst, wird, im Kontakt mit der Welt, mit Sprache, mit anderen Menschen. Und dieses Werden endet nicht mit der Kindheit. Das Gehirn bleibt ein Leben lang formbar; der Mensch lernt, verlernt und verwandelt sich bis ins Alter. Es gibt kein Wesen, das wir kennen, das sich selbst so weitreichend umbauen kann.</p>',
			'<p>Diese Unfertigkeit ist kein Defekt, den man wegoptimieren müsste. Sie ist die Bedingung von Wachstum, von Kreativität, von Freiheit. Ein Lebewesen fertigzustellen hieße, es anzuhalten: Wer den Menschen vollendet, überwindet ihn nicht — er friert ihn ein.</p>',
			'<p>Der transhumanistische Traum vom ewigen Leben ist deshalb kein mutiger Blick nach vorn. Er ist die Weigerung, dieses eine, unfertige, endliche Leben so zu gestalten, dass es nicht permanent nach Flucht verlangt. Die eigentliche Aufgabe ist nicht, den Menschen abzuschaffen. Sie ist, eine Welt zu bauen, in der das Unfertige nicht als Mangel erlitten werden muss, sondern als das gelebt werden kann, was es in Wahrheit ist: unsere Freiheit, noch nicht fertig zu sein.</p>',
		],
	];

	$sources = [
		'<strong>Altos Labs</strong> — 2022 mit 3 Mrd. USD Startfinanzierung gegründet (u. a. Jeff Bezos, Yuri Milner, ARCH Venture Partners); wissenschaftlicher Berater ist der Nobelpreisträger Shinya Yamanaka.',
		'<strong>Retro Biosciences</strong> — gegründet mit Seed-Kapital von Sam Altman; Series-A-Runde mit zuletzt aufgenommenem Kapital zu einer Bewertung von ca. 1,8 Mrd. USD (Stand Mai 2026); erklärtes Ziel: zehn zusätzliche gesunde Lebensjahre.',
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
