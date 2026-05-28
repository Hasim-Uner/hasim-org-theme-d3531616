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
		<span class="hp-kicker">Über mich</span>
		<h1 id="mission-title" class="hp-mission__title">Warum dieses Journal existiert</h1>
		<p class="hp-mission__lede">Mich treibt eine Frage um, die ich nicht abschütteln kann: Warum nutzen Menschen ihr Potenzial nicht?</p>
		<p class="hp-mission__lede">Nicht im Sinne von Karriere oder Leistung. Sondern im Sinne von Klarheit. Davon, sich aus Hass zu befreien. Nationalismus zu durchschauen. Hierarchien zu erkennen, bevor man ihnen gehorcht.</p>
		<p class="hp-mission__lede">Ich bin Kurde, in der Türkei (Nordkurdistan / Bakur) geboren, in Deutschland aufgewachsen. Kurdisch, Türkisch, Deutsch — drei Sprachen, drei Geschichten, die sich oft widersprechen. Das hat mir früh gezeigt: Wer wir sind, hängt stärker von den Narrativen ab, in denen wir aufgewachsen sind, als wir meistens bereit sind zuzugeben. Auch in Europa. Auch in Freiheit.</p>
		<p class="hp-mission__lede">Diese Fragen treiben dieses Journal.</p>
	</header>

	<div class="single-body single-body--with-toc hp-mission__frame">

		<aside class="hp-toc hp-mission__toc" aria-label="Inhaltsverzeichnis" data-visible="true">
			<span class="hp-toc__title">Struktur</span>
			<ol>
				<li><a href="#der-impuls">1. Der Impuls</a></li>
				<li><a href="#die-perspektive">2. Die Perspektive</a></li>
				<li><a href="#das-ziel">3. Das Ziel</a></li>
			</ol>
		</aside>

		<div class="single-body__main hp-mission__content">

			<section class="hp-mission__section" aria-labelledby="der-impuls">
				<h2 id="der-impuls">1. Der Impuls</h2>
				<p>Vieles in der heutigen Medienlandschaft wirkt auf mich erschöpft. Nicht, weil es zu wenig Inhalte gibt, sondern weil so vieles nur noch auf Reaktion gebaut ist: schnelle Erregung, kurze Empörung, kalkulierte Zuspitzung, Klicks, Lagerdenken.</p>
				<p>Es wird viel gesprochen, aber wenig wirklich gedacht.</p>
				<p>Aus meiner Beschäftigung mit Medienwissenschaft, Philosophie und gesellschaftlichen Dynamiken entstand deshalb das Bedürfnis, einen Gegenraum zu schaffen. Nicht aus Überlegenheit. Nicht, weil ich fertige Antworten besitze. Sondern weil ich selbst nach klareren Fragen suche.</p>
				<p>Dieses Journal ist Ausdruck dieses Suchens.</p>
				<p>Ich glaube nicht, dass wir die Welt, in die wir hineingeworfen wurden, einfach als selbstverständlich hinnehmen sollten. Die Systeme, Bilder, Begriffe und Erzählungen, die uns umgeben, formen unser Denken stärker, als wir oft wahrhaben wollen. Wer sie nicht untersucht, wird von ihnen untersucht. Wer sie nicht durchdringt, wird von ihnen gelenkt.</p>
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
				<p>Mein Blick auf gesellschaftliche Entwicklungen ist unweigerlich politisiert. Nicht im Sinne einer einfachen Parteihaltung, sondern durch Herkunft, Sprache, Geschichte und Reibung.</p>
				<p>Ich denke aus den Spannungen dreier Sprachräume heraus: Deutsch, Kurdisch und Türkisch. Wer die historischen Brüche, Konflikte, Verletzungen und feinen Nuancen dieser Welten im eigenen Denken ausbalancieren muss, verliert irgendwann die Geduld mit einfachen Antworten.</p>
				<p>Schwarz-Weiß-Denken wird schwer, wenn man gelernt hat, dass jede Geschichte mehrere Schichten hat.</p>
				<p>Diese Perspektive prägt mein Schreiben. Sie macht mich misstrauisch gegenüber allem, was zu glatt klingt. Gegenüber Erzählungen, die keine Brüche kennen. Gegenüber Analysen, die schon fertig sind, bevor sie angefangen haben. Gegenüber Meinungen, die nur deshalb stark wirken, weil sie nichts Komplexes an sich heranlassen.</p>
				<p>Ich will das Eindimensionale verweigern.</p>
				<p>Nicht aus Trotz, sondern aus Respekt vor der Wirklichkeit.</p>
			</section>

			<section class="hp-mission__section" aria-labelledby="das-ziel">
				<h2 id="das-ziel">3. Das Ziel</h2>
				<p>Dieses Journal ist als ruhiger, unabhängiger Gegenraum gebaut.</p>
				<p>Kein Tracking. Keine Klick-Köder. Kein digitaler Lärm. Keine künstliche Verknappung von Aufmerksamkeit. Nur Texte, Begriffe, Verbindungen und der ernsthafte Versuch, strukturierter zu denken.</p>
				<p>Das vernetzte Glossar ist dabei kein Beiwerk. Es ist Teil der Methode. Begriffe sollen nicht lose herumstehen, sondern Beziehungen bilden. Essays sollen nicht isoliert wirken, sondern ein wachsendes Denknetz ergeben. Mit jedem Text wird dieses Netz präziser.</p>
				<p>Wenn du hier einen Essay liest, triffst du nicht auf schnelle Meinungen, sondern auf Analysen, an denen gearbeitet wurde. Nicht perfekt. Nicht endgültig. Aber ernst gemeint.</p>
				<p>Dieses Journal ist eine Einladung, aus der alltäglichen Zerstreuung auszusteigen und sich wieder auf etwas einzulassen, das selten geworden ist: gedankliche Tiefe ohne Lärm.</p>
				<p>Nicht als Flucht aus der Welt.</p>
				<p>Sondern als Versuch, ihr genauer zu begegnen.</p>
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
