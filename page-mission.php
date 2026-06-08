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
				<p class="hp-mission__lede">Ich bin ein Mensch mit kurdischer Herkunft, in Deutschland aufgewachsen. Bewusst so herum: Herkunft verleugnet man nicht, aber man macht sie auch nicht zum Gefängnis. In mir stecken drei Welten — die kurdische, aus der ich komme; die türkische, deren Sprache und Medien mir vertraut sind, auch weil es die Sprache derer ist, die über das kurdische Volk herrschten; die deutsche, in der ich heute denke. Ich bin nicht assimiliert, aber ehrlich genug, dem Deutschen den tiefsten Abdruck zuzugestehen. Ein Volk, das im eigenen Land unterdrückt wird, dessen Sprache man zurückdrängt, lernt die der anderen. Der Ursprung ist bitter, der Vorteil bleibt: Man wächst in mehreren Welten zugleich auf. Wer drei davon mit all ihren Widersprüchen in sich trägt, kann keiner mehr glauben, sie sei die einzige wahre. Darum ist meine Heimat kein Land.</p>

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

				<p>Mit der Freiheit kam ich früh in Berührung, nicht aus einem Buch, sondern als etwas, das fehlt: Wenn deine Sprache, dein Name, deine Geschichte verdächtig sind, weißt du als Kind, was Unfreiheit ist, lange bevor du das Wort dafür hast.</p>

				<p>Als Migrant in Deutschland habe ich etwas gesehen, das man nicht mehr verlernt — dass die Gesetze, wenn es darauf ankommt, nicht für alle gleich gelten. Auf dem Papier herrscht Gleichheit; schaut man genauer hin — und als Kurdin oder Kurde sieht man es sofort —, wird mit zweierlei Maß gemessen. Was bei den einen Meinung ist, ist bei den anderen Gefahr. Auch die Neutralität des Rechts ist eine erzählte Geschichte — eine, an der niemand zweifelt, der nur die Seite kennt, für die sie stimmt. Daneben gibt es die leisere Unfreiheit, die kulturelle, die mediale — aber sie ist nur die sanfte Schwester derselben Sache.</p>

				<p>Daraus wächst leicht ein Misstrauen: Wenn jede Wahrheit nur erzählt ist, dann ist überall Macht, überall Konstruktion, nichts gilt. Ich kenne diese Haltung von innen — ich war lange selbst ein Zyniker und hielt es für Klarheit. Heute weiß ich, es war eine Sackgasse. Wer nur noch durchschaut, glaubt am Ende nichts mehr; das ist keine Freiheit, sondern Taubheit. Der Zyniker ist nicht frei — er hat nur aufgehört zu hören.</p>

				<p>Verdacht ist darum kein Ziel, sondern ein Werkzeug. Man durchschaut die Geschichten nicht, um recht zu behalten, sondern um freizulegen, was sie zudecken. Und zugedeckt wird zweierlei.</p>

				<p>Das eine liegt innen. Ich lese Nietzsches Willen zur Macht nicht als Lust am Herrschen, sondern als den Drang alles Lebendigen, sich zu entfalten — mehr zu werden, was es ist. Wahrhaftigkeit heißt dann nicht, die richtige Meinung zu haben, sondern sich selbst nicht zu belügen. Unfreiheit ist selten das Verbot. Sie ist das Kleinmachen, das sich als Tugend ausgibt — die Geschichte, die uns einredet, unsere Enge sei Anstand.</p>

				<p>Das andere liegt außen. Denn frei wird niemand allein. Freiheit ist gesellschaftlich, bevor sie individuell ist; sie braucht einen Boden, auf dem ein Einzelner überhaupt werden kann. Hier hat mich Abdullah Öcalan geprägt — nur wird er oft falsch gelesen, gerade in der kurdischen Gesellschaft: Man müsse jetzt Kommunen <em>bilden</em>, neue gründen. Aber die Kommune ist nichts, was man baut. Sie ist die kleinste Zelle jeder Gesellschaft, historisch gewachsen, immer schon da: deine Familie, dein Umfeld, das Team, in dem du arbeitest, die Menschen, mit denen du lebst. Die Frage ist nicht, ob du eine hast, sondern welche — wozu, auf welcher Basis, wie ihr leben wollt. Wer das nicht fragt, baut nur das nächste Gehäuse, das wieder vorschreibt, was sich gehört. Wer es gar nicht stellt, überlässt das Feld einem System, dem verwaltbare Einzelne lieber sind als Menschen, die sich selbst organisieren.</p>

				<p>Was eine Kommune dann zusammenhält, ist keine Wahrheit, die einer den anderen aufzwingt — das wäre nur das alte Spiel unter neuem Namen. Es ist eine Vereinbarung: Man trifft sich in der Mitte, im Wissen, dass es Dissonanz gibt und geben darf, einigt sich auf Prinzipien und lebt nach ihnen. Aber diese Prinzipien dürfen nicht erstarren. Das Gerüst muss tragen und zugleich offen bleiben — verbindlich genug, um Halt zu geben, beweglich genug, um nie zum Gehäuse zu werden. Struktur, die hält, ohne zu verschließen. Offen heißt aber nicht beliebig: Manches steht nicht zur Verhandlung — kein Leben zu nehmen, der Natur nicht zu schaden. Gute Regeln erkennt man daran, dass sie mit dem Lebendigen mitgehen statt gegen es; mit der Gesellschaft und mit der Natur, nicht über sie hinweg.</p>

				<p>Das Ziel ist einfach: dass jede Kommune in diesem Rahmen so leben darf, wie sie will — und wer mag, sich mit Gleichgesinnten eine neue bildet. Eine offene Gesellschaft, in der die Freiheit des Einzelnen und das Gemeinsame keine Gegensätze mehr sind. Darauf kommt es mir an.</p>

				<p>Darum bleibt die innere Freiheit der Prüfstein des Gemeinsamen. Und darum gilt der Verdacht zuerst den eigenen Leuten: Diese Unfreiheit ist nicht das Problem der anderen. Sie sitzt überall, wo eine Gruppe ihre Wahrheit für die einzige hält — auch in Bewegungen, die für die Freiheit kämpfen, meine eigene eingeschlossen. Das ist mein Eindruck, kein Urteil. Aber wo Abweichung sofort abgestempelt wird, ist der Raum des Denkbaren schon eng. Daneben lauert eine zweite Falle: der Pessimismus, der das bloße Ablehnen für Haltung hält. Wer nur verflucht, was schlecht läuft, gestaltet nichts — und radikal heißt nicht, alles abzulehnen, sondern an die Wurzel zu gehen, von radix: tief genug zu verstehen, um verändern zu können.</p>

				<p>Heute kommt erschwerend hinzu, dass kaum noch etwas durchdringt. Wir werden überflutet — Nachrichten, Meinungen, Reize —, und je mehr hereinbricht, desto kürzer wird der Blick: zu allem eine schnelle Meinung, zu nichts mehr die Ruhe, wirklich hinzusehen. Das geschieht schleichend, und es geschieht nicht von selbst. Ich glaube trotzdem nicht, dass die Welt schlecht ist oder der Mensch. Schlecht sind nur die künstlichen Ordnungen, die sich zwischen uns und die Welt schieben — und weil sie gemacht sind, lassen sie sich auch wieder abräumen.</p>

				<p>Denn darunter bleibt etwas, das sich nicht abschalten lässt. Räumt man weg, was es übertönt — innen wie außen —, kommt zum Vorschein, was die ganze Zeit da war: Resonanz, die Fähigkeit zu antworten, mitzuschwingen mit dem, was einen trifft. Eine Melodie, die einen Ort zurückbringt, den man vergessen hatte. Ein Gespräch, in dem man die Zeit vergisst. Ein Wald, in dem die eigene Eile klein wird. Ein Feuer, an dem keiner reden muss. Ein Saal voller Fremder, der nach einem wahren Satz still wird.</p>

				<p>Hartmut Rosa hält die Resonanz für etwas Unverfügbares, das einem zufällt oder ausbleibt. Ich sehe es anders: Sie ist nicht selten, sondern nur überlagert. Ihr Gegenteil ist nicht der Misston, sondern die Betäubung — wenn gar nichts mehr antwortet.</p>

				<p>Darum gehört auch die Dissonanz dazu. Resonanz heißt nicht Einklang, nicht dass alle dasselbe wollen. Wer aus Überzeugung widerspricht, schwingt mit — gegen den Strom, aber er antwortet; er ist lebendiger als jeder, der bloß nickt. Eine Gesellschaft ist nicht frei, wenn sie sich einig ist, sondern wenn sie das Anderssein aushält, statt es abzustellen. Das ist der Maßstab: nicht die Harmonie, sondern die Kraft, das Fremde zu ertragen und zu verstehen, auch das, was man nicht mag. Das Gegenteil davon ist der Satz „es ist halt, wie es ist" — der Zyniker, vom Ende her gedacht, und der Grund, warum ich keiner sein will.</p>

				<p>Ich schreibe nicht, um zu zeigen, was falsch ist, sondern um freizulegen, was bleibt. Was zugedeckt wird, ist nicht zerstört. Dieses Journal soll ein Resonanzraum sein — ein Ort, an dem wieder etwas antwortet, dem Lärm zum Trotz. Aber Resonanz entsteht nicht allein. Wenn dich hier etwas trifft oder stört, schreib mir.</p>
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
