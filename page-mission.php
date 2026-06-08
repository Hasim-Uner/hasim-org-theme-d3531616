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

				<p>Wer mit mehreren Welten aufwächst, lernt früh, keiner ganz zu glauben. Jede erzählt sich selbst als Mitte. Jede hält ihre Ordnung für natürlich. Jede hat ihre blinden Flecken — und jede ihre Schönheit. Ich kenne drei davon gut, und gerade deshalb will ich mich nicht auf eine festlegen. Meine Heimat ist kein Land. Sie ist die Vielfalt selbst.</p>

				<p>Mit Freiheit kam ich früh in Berührung — nicht als Idee, sondern als Mangel. Wenn deine Sprache, dein Name, deine Geschichte verdächtig sind, verstehst du Unfreiheit, lange bevor du ein Wort dafür hast.</p>

				<p>Später, als Migrant, kam eine zweite Lektion dazu. Auf dem Papier gilt Gleichheit. Wenn es darauf ankommt, gelten die Gesetze nicht für alle. Was bei den einen Meinung ist, ist bei den anderen Gefahr — als Kurdin oder Kurde sieht man das sofort. Der Staat, der sich für neutral hält, ist es nicht, wo es ihm unbequem wird.</p>

				<p>Lange dachte ich, Klarheit bedeute, alles zu durchschauen. Ich wurde zynisch und hielt das für Stärke. Heute glaube ich: Zynismus ist eine Form von Taubheit. Wer nur noch entlarvt, hört irgendwann nichts mehr.</p>

				<p>Verdacht bleibt notwendig. Aber er ist kein Ziel. Er ist ein Werkzeug. Man durchschaut Geschichten nicht, um über ihnen zu stehen, sondern um freizulegen, was sie verdecken.</p>

				<p>Was verdeckt wird, ist das Lebendige.</p>

				<p>Innen: die Fähigkeit, wahrhaftig zu werden, statt sich kleinzumachen und diese Enge Tugend zu nennen.</p>

				<p>Außen: die Fähigkeit, sich mit anderen so zu organisieren, dass Freiheit nicht gegen Gemeinschaft steht.</p>

				<p>Frei wird niemand allein. Jede Gesellschaft beginnt im Kleinen: in Familien, Freundschaften, Teams, Nachbarschaften, Kreisen. Die Frage ist nicht, ob wir in Kommunen leben. Die Frage ist, welche Art von Gemeinschaft wir zulassen — und ob sie Menschen öffnet oder einsperrt.</p>

				<p>Eine freie Gemeinschaft braucht keine Wahrheit, die alle schlucken müssen. Sie braucht Vereinbarungen, die tragen, ohne zu erstarren. Prinzipien, die Halt geben, ohne zum Gehäuse zu werden. Offenheit heißt nicht Beliebigkeit. Manches steht nicht zur Verhandlung: kein Leben zu nehmen, der Natur nicht zu schaden, Menschen nicht zu brechen.</p>

				<p>Das Ziel ist einfach: Räume, in denen Menschen anders sein dürfen, ohne ausgeschlossen zu werden. Räume, in denen Widerspruch nicht als Verrat gilt. Räume, in denen das Gemeinsame nicht den Einzelnen frisst — und der Einzelne nicht vergisst, dass er Teil eines Ganzen ist.</p>

				<p>Darum richtet sich mein Verdacht zuerst nach innen. Auch Bewegungen, die Freiheit wollen, können unfrei werden. Auch unterdrückte Menschen können Unterdrückung weitergeben. Auch die eigene Gruppe kann ihre Wahrheit zur einzigen machen.</p>

				<p>Radikal sein heißt für mich nicht, alles abzulehnen. Es heißt, an die Wurzel zu gehen. Tief genug zu verstehen, um verändern zu können.</p>

				<p>Heute wird das schwerer. Alles rauscht. Nachrichten, Meinungen, Reize, Empörung. Zu allem eine Haltung, zu wenig wirkliche Berührung. Der Blick wird kürzer. Die Stimme wird lauter. Die Antwort bleibt aus.</p>

				<p>Aber darunter ist nichts tot.</p>

				<p>Ich glaube nicht, dass der Mensch schlecht ist. Ich glaube, dass viele Ordnungen schlecht sind, weil sie uns von uns selbst, voneinander und von der Welt trennen. Und was gemacht wurde, kann auch wieder abgeräumt werden.</p>

				<p>Dann kommt etwas zum Vorschein, das die ganze Zeit da war: Resonanz.</p>

				<p>Hartmut Rosa beschreibt Resonanz als eine Beziehung zur Welt, in der uns etwas berührt und wir darauf antworten. Dieser Gedanke ist mir nah. Aber mein Resonanzbegriff ist weniger vorsichtig. Für mich ist Resonanz nicht selten und nicht bloß ein unverfügbares Ereignis, das einem zufällt oder ausbleibt. Sie ist grundsätzlicher: eine Fähigkeit des Lebendigen, die überlagert, betäubt und zugedeckt werden kann — aber nicht verschwindet.</p>

				<p>Ihr Gegenteil ist deshalb nicht der Misston. Ihr Gegenteil ist die Betäubung. Der Zustand, in dem nichts mehr antwortet.</p>

				<p>Darum gehört auch Dissonanz dazu. Resonanz heißt nicht Einklang. Nicht, dass alle dasselbe fühlen, denken oder wollen. Wer aus Überzeugung widerspricht, antwortet. Er schwingt mit — gegen den Strom, aber lebendig. Er ist freier als jeder, der bloß nickt.</p>

				<p>Eine Gesellschaft ist nicht frei, wenn alle einverstanden sind. Sie ist frei, wenn sie das Andere aushält, ohne es sofort abzustellen. Wenn sie Widerspruch nicht als Störung behandelt, sondern als Zeichen, dass noch etwas lebt.</p>

				<p>Dieses Journal soll ein solcher Raum sein: kein Ort fertiger Wahrheiten, sondern ein Resonanzraum. Wenn dich etwas trifft, stört oder zum Widerspruch bringt, schreib mir.</p>
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
