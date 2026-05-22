<?php
/**
 * Template Name: Mission
 *
 * @package Hasimuener_Journal
 * @version 7.0.0
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
		<span class="hp-kicker">Mission</span>
		<h1 id="mission-title" class="hp-mission__title">Mission</h1>
		<p class="hp-mission__lede">Dieses Journal ist kein Ort für Lagerdenken oder moralische Belehrungen. Es ist der Versuch, gesellschaftliche und technologische Dynamiken mit radikaler Klarheit zu sezieren – jenseits von algorithmischen Erregungsmustern.</p>
		<p class="hp-mission__structure-note" style="font-style: italic; margin-top: 1.5rem; opacity: 0.95; font-size: 1.05rem; border-left: 3px solid #b12a2a; padding-left: 1rem;">
			<strong>Die Struktur:</strong> Dieses Journal arbeitet als vernetztes System. Jeder tiefgehende Essay ist mit den datenbasierten Materialsammlungen der Dossiers untermauert und greift auf den präzisen Begriffsapparat des Glossars zurück.
		</p>
	</header>

	<div class="single-body single-body--with-toc hp-mission__frame">

		<aside class="hp-toc hp-mission__toc" aria-label="Inhaltsverzeichnis" data-visible="true">
			<span class="hp-toc__title">Struktur</span>
			<ol>
				<li><a href="#das-territorium">1. Das Territorium</a></li>
				<li><a href="#die-perspektive">2. Die Perspektive</a></li>
				<li><a href="#die-infrastruktur">3. Die Infrastruktur</a></li>
				<li><a href="#der-modus">4. Der Modus</a></li>
			</ol>
		</aside>

		<div class="single-body__main hp-mission__content">

			<section class="hp-mission__section" aria-labelledby="das-territorium">
				<h2 id="das-territorium">1. Das Territorium</h2>
				<p>Ich analysiere die Verwicklungen von Macht, Medien und Gesellschaft. Hier gibt es keine fertigen Antworten oder ideologischen Trost. Mich interessiert das System dahinter: Wie Narrative entstehen, wer von ihnen profitiert und wo die blinden Flecken unserer Gegenwart liegen.</p>
			</section>

			<section class="hp-mission__section hp-mission__section--with-portrait" aria-labelledby="die-perspektive">
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
				<h2 id="die-perspektive">2. Die Perspektive</h2>
				<p>Mein Blick ist durch die Bewegung zwischen drei Sprachen geprägt: Deutsch, Kurdisch und Türkisch. Das ist keine Folklore. Wer die historischen Brüche, Traumata und politischen Empfindlichkeiten dieser drei Räume permanent balancieren muss, verliert die Fähigkeit zu einfachen, eindimensionalen Feindbildern.</p>
			</section>

			<section class="hp-mission__section" aria-labelledby="die-infrastruktur">
				<h2 id="die-infrastruktur">3. Die Infrastruktur</h2>
				<p>Social-Media-Plattformen und ihre Algorithmen belohnen die schnelle Eskalation. Dieses Journal entzieht sich dieser Logik bewusst durch ein geschlossenes, hartcodiertes System. Kein Tracking, keine Klick-Optimierung. Nur Text, Netzwerk-Graph und Fokus. Das Ziel ist ein ungestörtes Lese-Ökosystem.</p>
			</section>

			<section class="hp-mission__section" aria-labelledby="der-modus">
				<h2 id="der-modus">4. Der Modus</h2>
				<p>Das Journal ist eine Einladung zum strukturierten Mitdenken und zum harten Widerspruch. Reibung bringt Schärfe. Wer hier liest, sucht keine Bestätigung, sondern Analysen, an denen sich das eigene Denken abarbeiten kann.</p>
			</section>

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
