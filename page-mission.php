<?php
/**
 * Template Name: Mission
 *
 * @package Hasimuener_Journal
 * @version 8.1.0
 */

get_header(); ?>

<?php while ( have_posts() ) : the_post(); ?>

<?php
$hp_essay_url   = get_post_type_archive_link( 'essay' );
$hp_note_url    = get_post_type_archive_link( 'note' );
$hp_contact_url = hp_get_contact_page_url();
?>

<main id="main-content" class="hp-mission" aria-labelledby="mission-title" role="main">

	<header class="hp-mission__hero">
		<span class="hp-kicker">Über mich</span>
		<h1 id="mission-title" class="hp-mission__title">Warum dieses Journal existiert</h1>
	</header>

	<div class="single-body hp-mission__frame">

		<div class="single-body__main hp-mission__content">

			<p class="hp-mission__lede">Die Frage, wie frei wir wirklich sind, kam für mich vor jedem Buch. Als Kurde wächst man mit ihr auf — bevor man weiß, dass es eine philosophische Frage ist. Welche Sprache du sprechen darfst. Welche Geschichte als wahr gilt. Welcher Name dir gehört. Erst später, mit Anfang zwanzig bei Nietzsche, hat sie eine Sprache bekommen, die zu ihr passte.</p>

			<p>Seitdem lässt sie mich nicht los: Wie frei sind wir wirklich? Nicht nur in dem, was wir tun dürfen — sondern in dem, was wir überhaupt denken können.</p>

			<p>Ich bin in der kurdischen Diaspora aufgewachsen, in der Freiheitsbewegung. Über Generationen wurde Kurdinnen und Kurden mit Gewalt die Identität abgesprochen — Sprachverbote, Massaker, Vertreibung, in vier Staaten zugleich. Was es heißt, für die eigene Existenz zu kämpfen, habe ich am eigenen Leib gespürt.</p>

			<p>Kurde zu sein ist für mich mehr als eine Identität. Es ist eine Beziehung zur Frage der Freiheit selbst. Und gerade deshalb weiß ich, wie schwer Befreiung ist. Sie ist kein Zustand, den man erreicht. Sie ist ein Prozess, der nie aufhört.</p>

			<p>Ich glaube: Wir sind mehr, als von uns erwartet wird. Mehr, als Konsum, Lagerdenken und vorgefertigte Rollen es zulassen. Das ist keine Klage — die Welt ist reich, das Leben ist schön. Aber etwas geht verloren. Fähigkeiten, die wir hatten. Verbindungen, die überlagert wurden.</p>

			<p>Eine davon ist Resonanz.</p>

			<p>Resonanz ist nicht Stimulation. Kein Dopaminschub, kein Scroll-Reflex, kein kurzes Aufflackern von Erregung. Resonanz ist das Gegenteil: der Moment, in dem etwas wirklich antwortet — in dir und zwischen dir und der Welt. Sie ist kein Ausnahmezustand. Sie ist der Grundzustand. Sie wird nur gestört — von einem Lärm, der sich nach Erlebnis anfühlt, es aber nicht ist.</p>

			<p>Je näher man der Natur ist, desto mehr wird man sich selbst. Du weißt das. Was uns mit allem verbindet, ist keine Tugend, sondern eine Grundresonanz zum Dasein — die Stoiker nannten sie <a href="<?php echo esc_url( home_url( '/glossar/sympatheia/' ) ); ?>">Sympatheia</a>.</p>

			<figure class="hp-mission__portrait" role="group" aria-label="Porträt Haşim Üner">
				<img
					src="https://hasimuener.org/wp-content/uploads/2026/05/Hasim-Uener_portait.png"
					alt="Porträt Haşim Üner"
					loading="lazy"
					decoding="async"
					width="320"
					height="320">
				<figcaption>
					<span class="hp-mission__portrait-name">Haşim Üner</span>
					<span class="hp-mission__portrait-meta">Hannover · Autor dieses Journals</span>
				</figcaption>
			</figure>

			<p>Geboren in der Türkei (<a href="<?php echo esc_url( home_url( '/glossar/nordkurdistan/' ) ); ?>">Nordkurdistan</a> / Bakur), aufgewachsen in Deutschland. Kurdisch, Türkisch, Deutsch — drei Sprachen, drei Geschichten, die sich oft widersprechen. Wer so aufwächst, verliert irgendwann die Geduld mit einfachen Antworten. Und gewinnt dafür etwas anderes: den Blick für das, was Narrative verdecken. Für das, was Hierarchien unsichtbar halten. Für die Risse im Selbstverständlichen.</p>

			<p>Mein Blick auf Gesellschaft ist unweigerlich politisiert — nicht durch Parteihaltung, sondern durch Herkunft, Sprache, Geschichte und Reibung. Wer die historischen Brüche dreier Welten im eigenen Denken ausbalancieren muss, verliert irgendwann die Geduld mit Schwarz-Weiß.</p>

			<p>Dieses Journal ist der Versuch, mit Vernunft zu fragen — nicht um Recht zu haben, sondern um klarer zu sehen. Kein Tracking. Keine Klick-Köder. Nur Texte, Begriffe, Verbindungen — und ein Plädoyer für Vernunft als menschliches Vermögen, nicht als Kälte.</p>

			<section class="hp-mission__closing" aria-label="Abschluss und nächste Schritte">
				<div class="hp-mission__cta" aria-label="Nächste Schritte">
					<div class="hp-mission__cta-grid">
						<a class="hp-mission__cta-card" href="<?php echo esc_url( $hp_essay_url ); ?>">
							<span class="hp-mission__cta-title">Zu den Essays</span>
						</a>

						<a class="hp-mission__cta-card" href="<?php echo esc_url( $hp_note_url ); ?>">
							<span class="hp-mission__cta-title">Zu den Notizen</span>
						</a>

						<a class="hp-mission__cta-card" href="<?php echo esc_url( $hp_contact_url ); ?>">
							<span class="hp-mission__cta-title">Anfragen &amp; Zusammenarbeit</span>
						</a>
					</div>
				</div>
			</section>

		</div>
	</div>

</main>

<?php endwhile; ?>

<?php get_footer(); ?>
