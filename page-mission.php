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

			<p class="hp-mission__lede">Die Frage nach der Freiheit kam für mich vor jedem Buch. Als Kurde wächst man mit ihr auf, bevor man weiß, dass sie eine philosophische Frage ist: welche Sprache du sprechen darfst, welche Geschichte als wahr gilt, wer du sein darfst. Erst mit Anfang zwanzig, bei Nietzsche, bekam sie eine Sprache.</p>

			<p>Lange war das eine Frage nach äußerer Freiheit — nach dem, was man tun, sagen, sein darf. Diese Unfreiheit ist sichtbar; sie hat einen Namen und einen Gegner. Inzwischen interessiert mich die Frage darunter: nicht, was wir tun dürfen, sondern was wir überhaupt denken können. Sie ist schwerer zu fassen, weil das Werkzeug, mit dem man sie untersucht — das eigene Denken — selbst geprägt wurde: von der Sprache, in der man aufwuchs, von der Position, in die man gestellt wurde, von dem, woran man sich gewöhnt hat.</p>

			<p>Geboren in der Türkei (<a href="<?php echo esc_url( home_url( '/glossar/nordkurdistan/' ) ); ?>">Nordkurdistan</a> / Bakur), aufgewachsen in Deutschland. Kurdisch, Türkisch, Deutsch — drei Sprachen, drei Geschichten, die sich oft widersprechen. Wer so aufwächst, verliert die Geduld mit einfachen Antworten und gewinnt den Blick für das, was Narrative verdecken und Hierarchien unsichtbar halten. Ich kann nicht so tun, als stünde ich außerhalb: Über meine Sprache, meinen Namen, meine Geschichte wurde entschieden, bevor ich mitreden konnte. Das ist kein Standpunkt, den ich gewählt habe — es ist die Lage, aus der ich sehe.</p>

			<p>Was uns einschränkt, ist nicht nur das, was verboten wird. Es ist das, was sich als selbstverständlich tarnt. Nicht jede Ordnung ist das Problem; gewachsene, getragene Autorität gibt es auch. Aber die erstarrten Hierarchien — die Monopole aus Staat, Kapital und Tradition, die Hegemonie darüber, was als gut zu gelten hat — greifen tiefer als in unsere Rechte: Sie formen, was wir denken, welche Räume wir betreten, wie wir mit dem eigenen Körper umgehen. Vieles davon wird uns als Moral verkauft, die keine ist — oder als Glück: das fertige Skript aus Besitz, Status und Konkurrenz, das niemanden satt macht. Beides ist nur Ordnung, die sich Sinn gibt. Aber keine Ordnung fasst alles. Sie hat Lücken, an denen sie nicht greift. Was sie überlagert, kann sie nicht zerstören. Und sie überlagert ohne Pause: Das Rauschen aus Reizen und Feeds muss ständig erneuert werden — gerade dieser Daueraufwand zeigt, wie nah das Übertönte liegt. Nichts davon muss sein.</p>

			<p>Denn darunter bleibt etwas, das sich nicht stilllegen lässt: Resonanz und Werden. Resonanz ist die Fähigkeit zu antworten — mitzuschwingen mit dem, was einen trifft. Auch Dissonanz gehört dazu: Dass ein Mensch dir nicht geheuer ist, dass du Gefahr spürst, ist genauso ein Antworten wie das Gefühl, dass etwas trägt. Der Mensch ist ein Resonanzkörper, physisch wie geistig. Werden ist die Bewegung, sich nicht festzustellen, sondern weiterzugehen. Beides wird gestört, überlagert, übertönt — aber es ist da, bei einzelnen Menschen, in einzelnen Gemeinschaften. Das Gegenteil von Resonanz ist darum nicht die Dissonanz, sondern die Betäubung: die Überflutung, in der alles gleich rauscht und nichts mehr antwortet.</p>

			<p>Hier trenne ich mich von Hartmut Rosa. Für ihn ist Resonanz ein unverfügbares Ereignis — etwas, das einem widerfährt oder ausbleibt und das sich nicht herstellen lässt. Für mich ist sie keine Ausnahme, sondern der Normalfall: immer schon da, nur überlagert. Erzwingen kann man sie nicht — aber man kann das Rauschen wegräumen, das sie übertönt. Nicht ein Ereignis, auf das man wartet, sondern ein Grund, den man freilegt. Rosas Diagnose der Spätmoderne teile ich. Seinen metaphysischen Überbau brauche ich nicht.</p>

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

			<p>Ich bin nicht der Typ, der sagt: Weil das System manipuliert, ist alles verloren. Es manipuliert — und scheitert trotzdem daran, den Menschen ganz zu fassen. Was uns ausmacht, sitzt tiefer als jede Ordnung: Empathie, Zuneigung, Kreativität, die Liebe zum Dasein selbst. Diese Vermögen sind in jeder Kultur dieselben — älter als jede Grenze, jede Sprache, jeder Staat. Das ist keine fromme Hoffnung, sondern das, was Jahrhunderttausende in uns abgelegt haben. Überlagern lässt es sich. Kaputtmachen nicht.</p>

			<p>Und es ist mehr als das, was übrig bleibt — es ist der Weg hinaus. Wer wieder spürt, was trägt, löst sich nach und nach aus dem, was ihn überformt: nicht im Kampf dagegen, sondern indem das Lebendige zurückkehrt. So wächst Resonanz. So kommt das Werden wieder in Bewegung.</p>

			<p>Ich schreibe seit Jahren, zuerst nur für mich. Ich nehme Begriffe auseinander, um zu verstehen — darin bin ich radikal. Das täte ich auch, wenn niemand mitläse. Veröffentlichen tue ich aus einem zweiten Grund: weil das, was beim Verstehen sichtbar wird, gegen die Resignation steht. Ich will nicht entlarven, was falsch ist, sondern freilegen, was bleibt. Wer die Überlagerung durchschaut, sieht auch das, was sie überlagert.</p>

			<p>Freiheit ist darum nicht die Abwesenheit von Form. Wer sich von jeder Form lösen will, landet nicht in Freiheit, sondern in Beliebigkeit — und Beliebigkeit ist nur Rauschen unter anderem Namen. Es geht darum, die geerbten, unbewussten Formen durch selbst gewählte zu ersetzen: feste Formen, offene Gründe. An der Praxis festhalten, ohne ihre Begründung für unantastbar zu erklären.</p>

			<p>Dieses Journal ist der Versuch, genau zu fragen — nicht um recht zu haben, sondern um klarer zu sehen. Mit einem Denken, das aus Resonanz kommt statt aus Betäubung: das sich von seinem Gegenstand noch berühren lässt, statt ihn nur zu vermessen. Kein Tracking, keine Klick-Köder. Nur Texte, Begriffe, Verbindungen.</p>

			<p>Noch ist das ein Monolog. Aber ich schreibe nicht, um recht zu behalten, sondern um Gegenüber zu finden — denn Wissen wächst nicht im Konsens, sondern im Widerspruch, und Widerspruch ist auch ein Antworten. Wenn dich hier etwas trifft, schreib mir. Wohin das führt, will ich nicht allein entscheiden.</p>

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
