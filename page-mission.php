<?php
/**
 * Template Name: Mission
 *
 * @package Hasimuener_Journal
 * @version 8.2.0
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

			<?php /* ── Raum 1: Freiheit ── */ ?>
			<p class="hp-mission__lede">Die Frage nach der Freiheit kam für mich vor jedem Buch. Als Kurde wächst man mit ihr auf, bevor man weiß, dass sie eine philosophische Frage ist: welche Sprache du sprechen darfst, welche Geschichte als wahr gilt, wer du sein darfst. Erst mit Anfang zwanzig, bei Nietzsche, bekam sie eine Sprache.</p>

			<p>Lange war das eine Frage nach äußerer Freiheit — nach dem, was man tun, sagen, sein darf. Diese Unfreiheit ist sichtbar; sie hat einen Namen und einen Gegner. Inzwischen interessiert mich die Frage darunter: nicht, was wir tun dürfen, sondern was wir überhaupt denken können. Sie ist schwerer zu fassen, weil das Werkzeug, mit dem man sie untersucht — das eigene Denken — selbst das ist, was geprägt wurde. Von der Sprache, in der man aufwuchs. Von der Position, in die man gestellt wurde. Von dem, woran man sich selbst gewöhnt hat.</p>

			<hr class="hp-mission__separator">

			<?php /* ── Raum 2: Resonanz ── */ ?>
			<p>Es geht mir nicht darum, das zu durchschauen und dann resigniert dazustehen. Durchschauen ist nur die eine Hälfte — die Befreiung von etwas. Die andere ist, selbst etwas zu bauen: schön zu denken, mit allem, was Menschsein ausmacht — Kreativität, Empathie, der Sinn für Stimmigkeit.</p>

			<p>Für diese Stimmigkeit habe ich einen Namen: Resonanz. Das Gefühl, dass etwas zu dir passt, durch dich hindurchgeht, antwortet. Das Knistern eines Feuers, die Wärme einer Hand, der Geruch von Regen auf warmer Erde. Ihr Gegenteil ist nicht die Stille, sondern die Überflutung — das Rauschen, das uns nicht mehr unterscheiden lässt, was wirklich trifft und was nur reizt. Darin unterscheide ich mich von Hartmut Rosa, der Resonanz zum „unverfügbaren Ereignis" erhebt. Die Diagnose teile ich. Den metaphysischen Überbau brauche ich nicht.</p>

			<p>Freiheit ist für mich darum nicht die Abwesenheit von Form. Wer sich von jedem System lösen will, landet nicht in Freiheit, sondern in Beliebigkeit — und Beliebigkeit ist nur Rauschen unter anderem Namen. Es geht darum, die geerbten, unbewussten Formen durch selbst gewählte zu ersetzen: feste Formen, offene Gründe. An der Praxis festhalten, ohne ihre Begründung für unantastbar zu erklären.</p>

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

			<hr class="hp-mission__separator">

			<?php /* ── Raum 3: Mehrsprachigkeit & Perspektive ── */ ?>
			<p>Geboren in der Türkei (<a href="<?php echo esc_url( home_url( '/glossar/nordkurdistan/' ) ); ?>">Nordkurdistan</a> / Bakur), aufgewachsen in Deutschland. Kurdisch, Türkisch, Deutsch — drei Sprachen, drei Geschichten, die sich oft widersprechen. Wer so aufwächst, verliert die Geduld mit einfachen Antworten und gewinnt den Blick für das, was Narrative verdecken und Hierarchien unsichtbar halten. Mein Blick ist politisiert — nicht durch Parteihaltung, sondern durch Herkunft, Sprache und Reibung.</p>

			<hr class="hp-mission__separator">

			<?php /* ── Raum 4: Journal ── */ ?>
			<p>Dieses Journal ist der Versuch, mit Vernunft zu fragen — nicht um recht zu haben, sondern um klarer zu sehen. Kein Tracking, keine Klick-Köder. Nur Texte, Begriffe, Verbindungen — und ein Plädoyer für Vernunft als menschliches Vermögen, nicht als Kälte.</p>

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
