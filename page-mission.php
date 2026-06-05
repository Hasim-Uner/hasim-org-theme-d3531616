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

$hp_mission_audio_id  = 193;
$hp_mission_audio_url = wp_get_attachment_url( $hp_mission_audio_id );
?>

<main id="main-content" class="hp-mission" aria-labelledby="mission-title" role="main">

	<header class="hp-mission__hero">
		<span class="hp-kicker">Über mich</span>
		<h1 id="mission-title" class="hp-mission__title">Warum dieses Journal existiert</h1>

		<?php if ( $hp_mission_audio_url ) : ?>
			<div class="hp-mission-audio" data-hp-audio aria-label="Audiofassung">
				<div class="hp-mission-audio__top">
					<button class="hp-mission-audio__button" type="button" data-hp-audio-toggle aria-pressed="false">
						<span class="hp-mission-audio__icon" data-hp-audio-icon aria-hidden="true"></span>
						<span data-hp-audio-label>Mission anhören</span>
					</button>

					<div class="hp-mission-audio__copy">
						<span class="hp-mission-audio__eyebrow">Audiofassung</span>
						<span class="hp-mission-audio__title">Warum dieses Journal existiert</span>
					</div>
				</div>

				<div class="hp-mission-audio__progress" role="progressbar" aria-label="Audiofortschritt" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" data-hp-audio-progress>
					<span class="hp-mission-audio__progress-bar" data-hp-audio-progress-bar></span>
				</div>

				<div class="hp-mission-audio__meta">
					<span class="hp-mission-audio__status" data-hp-audio-status>Bereit</span>
					<span class="hp-mission-audio__time" data-hp-audio-time>0:00 / 0:00</span>
				</div>

				<audio class="hp-mission-audio__media" preload="auto" controls data-hp-audio-media>
					<source src="<?php echo esc_url( $hp_mission_audio_url ); ?>" type="audio/mpeg">
				</audio>

				<a class="hp-mission-audio__direct" href="<?php echo esc_url( $hp_mission_audio_url ); ?>" target="_blank" rel="noopener noreferrer">Audio direkt öffnen</a>
			</div>
		<?php endif; ?>
	</header>

	<div class="single-body hp-mission__frame">

		<div class="single-body__main hp-mission__content">

			<p class="hp-mission__lede">Die Frage nach der Freiheit kam für mich nicht aus einem Buch, sondern aus dem Leben. Das Kurdische wird unterdrückt: Wer sich anpasst, lebt in Ruhe; wer dazu steht, zahlt dafür. Diese harte Form habe ich nicht erlitten — ich bin in Deutschland aufgewachsen. Was ich kenne, ist die leisere Unfreiheit: die kulturelle, die mediale.</p>

			<p>Kurdisch, Türkisch, Deutsch — drei Sprachen, drei Geschichten. Das macht einen nicht heimatlos; es ändert den Blick. Man merkt, dass das, was jede Welt für wahr hält, eine erzählte Geschichte ist — eine, über die niemand zweifelt, der nur diese eine kennt.</p>

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

			<p>Lange hielt ich Freiheit für etwas Äußeres: was man tun, sagen, sein darf. Heute interessiert mich die darunter — nicht, was wir tun dürfen, sondern was wir überhaupt denken können. Was uns einschränkt, ist selten ein Verbot. Es sind die Geschichten, die in der Mitte der Gesellschaft sitzen und überall wiederholt werden — in der Familie, in der Schule, in den Medien —, bis sie wie die Wirklichkeit aussehen; und für jeden gibt es die passende. Wer sie bezweifelt, gilt schnell als naiv oder als Störer; so ist die Welt eben, das gehört sich nicht. Diese Unfreiheit ist nicht das Problem der anderen. Sie sitzt überall, wo eine Gruppe ihre Wahrheit für die einzige hält — auch in Bewegungen, die für die Freiheit kämpfen, meine eigene eingeschlossen. Das ist mein Eindruck, kein Urteil. Aber wo Abweichung sofort abgestempelt wird, ist der Raum des Denkbaren schon eng geworden.</p>

			<p>Wir werden heute mit so viel überflutet — Nachrichten, Meinungen, Reizen —, dass am Ende nichts mehr wirklich ankommt. Und doch bleibt darunter etwas, das sich nicht abschalten lässt: Resonanz — die Fähigkeit zu antworten, mitzuschwingen mit dem, was einen trifft. Eine Melodie, die einen Ort zurückbringt, den man vergessen hatte. Ein Gespräch, in dem man die Zeit vergisst. Ein Satz, der einen anhält, als wäre er für einen geschrieben. Ein Wald, in dem die eigene Eile klein wird. Ein Feuer, an dem keiner reden muss. Solche Augenblicke meine ich — die, in denen die Welt einen Moment lang zurückspricht. Hartmut Rosa hält sie für etwas Unverfügbares, das einem zufällt oder ausbleibt. Ich sehe es anders: Sie ist immer da, nur übertönt. Man muss sie nicht erzwingen — man muss nur wegräumen, was sie zudeckt. Ihr Gegenteil ist nicht der Misston, sondern die Betäubung: der Lärm, in dem nichts mehr antwortet.</p>

			<p>Ich schreibe nicht, um zu zeigen, was falsch ist, sondern um freizulegen, was bleibt. Was zugedeckt wird, ist nicht zerstört. Dieses Journal soll ein Resonanzraum sein — ein Ort, an dem wieder etwas antwortet, dem Lärm zum Trotz. Aber Resonanz entsteht nicht allein. Wenn dich hier etwas trifft oder stört, schreib mir.</p>

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
