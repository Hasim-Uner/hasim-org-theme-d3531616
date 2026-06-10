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

$hp_mission_audio_url = 'https://hasimuener.org/wp-content/uploads/2026/06/mission-audio-wordpress-320kbps.mp3';
?>

<main id="main-content" class="hp-mission" aria-labelledby="mission-title" role="main">

	<header class="hp-mission__hero">
		<span class="hp-kicker">Über mich</span>
		<h1 id="mission-title" class="hp-mission__title">Mission</h1>

		<?php if ( $hp_mission_audio_url ) : ?>
			<div class="hp-mission-audio" data-hp-audio aria-label="Audiofassung">
				<div class="hp-mission-audio__top">
					<button class="hp-mission-audio__button" type="button" data-hp-audio-toggle aria-pressed="false">
						<span class="hp-mission-audio__icon" data-hp-audio-icon aria-hidden="true"></span>
						<span data-hp-audio-label>Mission anhören</span>
					</button>

					<div class="hp-mission-audio__copy">
						<span class="hp-mission-audio__eyebrow">Audiofassung</span>
						<span class="hp-mission-audio__title">Mission</span>
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

			<section class="hp-mission__section hp-mission__section--with-portrait" aria-label="Missionstext">
				<p class="hp-mission__lede">Ich komme aus mehreren Welten. Aus der kurdischen, in die ich hineingeboren wurde. Aus der türkischen, deren Sprache und Medien mir vertraut sind — auch, weil es die Sprache derer ist, die über das kurdische Volk herrschten. Und aus der deutschen, in der ich heute denke.</p>

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

				<p>Ich bin nicht assimiliert. Aber wer als Kind herkommt und hier aufwächst, in dem hinterlässt das Deutsche den tiefsten Abdruck. So ist es bei mir.</p>

				<p>Wer mit mehreren Welten aufwächst, lernt früh, keiner ganz zu glauben. Jede erzählt sich selbst als Mitte. Jede hält ihre Ordnung für natürlich. Jede hat ihre blinden Flecken — und jede ihre Schönheit. Ich kenne drei davon gut, und gerade deshalb lege ich mich auf keine fest.</p>

				<p>Mit Freiheit kam ich früh in Berührung — nicht als Idee, sondern als Mangel. Wenn deine Sprache, dein Name, deine Geschichte verdächtig sind, verstehst du Unfreiheit, lange bevor du ein Wort dafür hast. Später kam eine zweite Lektion dazu: Auf dem Papier gilt Gleichheit. Wenn es darauf ankommt, gelten die Gesetze nicht für alle. Was bei den einen Meinung ist, ist bei den anderen Gefahr. Der Staat, der sich für neutral hält, ist es nicht, wo es ihm unbequem wird.</p>

				<p>Lange dachte ich, Klarheit bedeute, alles zu durchschauen. Ich wurde zynisch und hielt das für Stärke. Heute glaube ich: Zynismus ist eine Form von Taubheit. Wer nur noch entlarvt, hört irgendwann nichts mehr.</p>

				<p>Verdacht bleibt notwendig. Aber er ist ein Werkzeug, kein Ziel. Man durchschaut Geschichten nicht, um über ihnen zu stehen, sondern um freizulegen, was sie verdecken: das Lebendige.</p>

				<p>Ich glaube nicht, dass der Mensch schlecht ist. Ich glaube, dass viele Ordnungen schlecht sind, weil sie uns von uns selbst, voneinander und von der Welt trennen. Und was gemacht wurde, kann auch wieder abgeräumt werden. Dann kommt zum Vorschein, was die ganze Zeit da war: die Fähigkeit, berührt zu werden und zu antworten. Man kann sie überlagern, betäuben, zudecken. Aber sie verschwindet nicht.</p>

				<p>Antworten heißt nicht zustimmen. Wer aus Überzeugung widerspricht, antwortet auch. Er schwingt mit — gegen den Strom, aber lebendig. Er ist freier als jeder, der bloß nickt.</p>

				<p>Eine Gesellschaft ist nicht frei, wenn alle einverstanden sind. Sie ist frei, wenn sie das Andere aushält, ohne es abzustellen — wenn sie Widerspruch nicht als Störung behandelt, sondern als Zeichen, dass noch etwas lebt.</p>

				<p>Dieses Journal soll ein solcher Raum sein: kein Ort fertiger Wahrheiten, sondern ein Resonanzraum. Hier schreibe ich über Macht, Medien, Sprache und Erinnerung — und darüber, was sie verdecken. Wenn dich etwas trifft, stört oder zum Widerspruch bringt, schreib mir.</p>
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
