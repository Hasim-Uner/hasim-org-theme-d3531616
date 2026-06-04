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

			<p class="hp-mission__lede">Man kann über Freiheit unendlich nachdenken, ohne dass sie je zur eigenen Frage wird. Bei mir wurde sie es früh — nicht durch ein Buch, sondern durchs Leben. Das Kurdische wird unterdrückt: Wer sich anpasst, lebt in Ruhe; wer darauf besteht, stößt an Grenzen. Die harte Form habe ich nicht erlitten, ich bin in Deutschland aufgewachsen. Was ich kenne, ist die leisere Unfreiheit: die kulturelle, die mediale.</p>

			<p>Kurdisch, Türkisch, Deutsch — drei Sprachen, drei Geschichten. Das macht einen nicht heimatlos und nicht überlegen; es ändert den Blick. Wer mehrere Welten von innen kennt, sieht, wie verschieden Menschen ihre Geschichten erzählen — und wie viel sie darin teilen. Man lernt, dass kaum etwas so selbstverständlich ist, wie es die eigene Seite glauben macht. Das Kurdische bleibt mir Grund, nicht Gepäck — aber keine Nation allein sagt mir, wer ich bin. Eine Ordnung, die einen nur duldet, wenn man wird wie sie, lehrt das Misstrauen gegen jede.</p>

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
					<span class="hp-mission__portrait-meta">Autor dieses Journals</span>
				</figcaption>
			</figure>

			<p>Genau dieses Sehen führt zur eigentlichen Frage. Lange hielt ich Freiheit für etwas Äußeres: was man tun, sagen, sein darf. Heute interessiert mich die darunter — nicht, was wir tun dürfen, sondern was wir überhaupt denken können. Denn was uns einschränkt, ist selten das Verbotene. Es ist das, was sich als selbstverständlich ausgibt: als Moral, als Glück, als das fertige Leben aus Besitz und Status, das niemanden satt macht.</p>

			<p>Und doch fasst keine Ordnung den Menschen ganz. Etwas bleibt, das sich nicht abschalten lässt: Resonanz — die Fähigkeit zu antworten, mitzuschwingen mit dem, was einen trifft. Hartmut Rosa hält sie für ein seltenes Ereignis, das einem zufällt. Ich sehe es anders: Sie ist immer da, nur übertönt. Erzwingen kann man sie nicht — aber das Rauschen, das sie zudeckt, lässt sich wegräumen. Ihr Gegenteil ist nicht der Misston, sondern die Betäubung: der Lärm, in dem nichts mehr antwortet.</p>

			<p>Darum schreibe ich: nicht um zu zeigen, was falsch ist, sondern um freizulegen, was bleibt — denn was zugedeckt wird, ist nicht zerstört. Nicht um recht zu behalten, sondern um ein Gegenüber zu finden. Wenn dich hier etwas trifft oder stört, schreib mir.</p>

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
