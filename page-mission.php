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
			<p class="hp-mission__lede">Die Frage, wie frei wir wirklich sind, kam für mich vor jedem Buch. Als Kurde wächst man mit ihr auf — bevor man weiß, dass es eine philosophische Frage ist. Welche Sprache du sprechen darfst. Welche Geschichte als wahr gilt. Wer du sein darfst. Erst später, mit Anfang zwanzig bei Nietzsche, hat sie eine Sprache bekommen, die zu ihr passte.</p>

			<p>Kurde zu sein ist für mich mehr als Identität — es ist eine Beziehung zur Frage der Freiheit selbst. Und die geht tiefer, als sie zunächst scheint: nicht nur, was wir tun dürfen, sondern was wir überhaupt denken können. Gerade deshalb weiß ich, wie schwer Befreiung ist. Sie ist kein Zustand, den man erreicht, sondern ein Prozess, der nie aufhört.</p>

			<hr class="hp-mission__separator">

			<?php /* ── Raum 2: Resonanz ── */ ?>
			<p>Wir sind mehr, als von uns erwartet wird. Mehr, als Konsum, Lagerdenken und vorgefertigte Rollen es zulassen. Das ist keine Klage. Das Dasein ist nicht arm — es ist überwältigend in seiner Tiefe und Form. Die Welt ist nicht öde — sie ist voller Bewegung, Möglichkeit, Bedeutung. Aber etwas in unserem Verhältnis zu ihr ist verstellt worden.</p>

			<p>Eine davon ist Resonanz.</p>

			<p>Resonanz ist Stimmigkeit. Das Gefühl, dass etwas zu dir passt, durch dich hindurchgeht, antwortet. Das Knistern eines Lagerfeuers. Die Wärme einer Hand in deiner. Der Geruch von Regen auf warmer Erde. Eine Melodie, die dich seit Jahren begleitet. Die Stille kurz vor einem Gewitter. Sie ist körperlich spürbar — manchmal als Kribbeln, manchmal nur als Ruhe.</p>

			<p>Das Gegenteil von Resonanz ist nicht ihre Abwesenheit. Es ist Überflutung. Das ständige Rauschen aus Ängsten, Wünschen, Informationen, das uns nicht mehr unterscheiden lässt, was wirklich trifft und was nur reizt.</p>

			<p>Resonanz ist nicht rar. Sie ist immer da — wie ein Strom, der durch uns geht. Sie braucht nur Stille und bewusstes Wahrnehmen. Bewusstsein, Kreativität, Entdeckungskraft — ich glaube, dass all das davon lebt, dass wir im Kern resonanzfähige Wesen sind.</p>

			<p>Damit unterscheide ich mich von Hartmut Rosa, der Resonanz mit theologischem Vokabular zum „konstitutiv unverfügbaren Ereignis" erhebt. Seine Diagnose, dass die Spätmoderne sie verstellt, teile ich. Den metaphysischen Überbau brauche ich nicht.</p>

			<p>Je näher man der Natur ist, desto mehr wird man sich selbst. Kennst du das? Was uns mit allem verbindet, ist in meiner Lesart keine Tugend, sondern eine Grundresonanz zum Dasein — die Stoiker nannten sie <a href="<?php echo esc_url( home_url( '/glossar/sympatheia/' ) ); ?>">Sympatheia</a>.</p>

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
			<p>Geboren in der Türkei (<a href="<?php echo esc_url( home_url( '/glossar/nordkurdistan/' ) ); ?>">Nordkurdistan</a> / Bakur), aufgewachsen in Deutschland. Kurdisch, Türkisch, Deutsch — drei Sprachen, drei Geschichten, die sich oft widersprechen. Wer so aufwächst, verliert irgendwann die Geduld mit einfachen Antworten. Und gewinnt dafür etwas anderes: den Blick für das, was Narrative verdecken. Für das, was Hierarchien unsichtbar halten. Für die Risse im Selbstverständlichen.</p>

			<p>Mein Blick auf Gesellschaft ist unweigerlich politisiert — nicht durch Parteihaltung, sondern durch Herkunft, Sprache, Geschichte und Reibung.</p>

			<hr class="hp-mission__separator">

			<?php /* ── Raum 4: Journal ── */ ?>
			<p>Dieses Journal ist der Versuch, genau zu fragen — nicht um Recht zu haben, sondern um klarer zu sehen. Kein Tracking. Keine Klick-Köder. Nur Texte, Begriffe, Verbindungen — und ein Plädoyer für Vernunft als menschliches Vermögen, nicht als Kälte.</p>

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
