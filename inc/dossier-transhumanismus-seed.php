<?php
/**
 * Dossier Seed — Transhumanismus
 *
 * Idempotentes Anlegen des Dossiers „Transhumanismus — die Flucht aus dem Menschen".
 * v3-final: vollständiger Begriffsapparat (19 Terme), Quellen aus Forschungsdokument,
 * Status publish, Hook-Priorität 30 (nach Glossar-Seed bei 25).
 *
 * @package Hasimuener_Journal
 */

defined( 'ABSPATH' ) || exit;

const HP_DOSSIER_TRANSHUMANISMUS_SEED_VERSION = 'v3-final';

function hp_run_dossier_transhumanismus_seed_once(): void {
	if ( ! is_admin() ) {
		return;
	}

	if ( get_option( 'hp_dossier_transhumanismus_seed_version' ) === HP_DOSSIER_TRANSHUMANISMUS_SEED_VERSION ) {
		return;
	}

	hp_seed_transhumanismus_dossier();

	update_option( 'hp_dossier_transhumanismus_seed_version', HP_DOSSIER_TRANSHUMANISMUS_SEED_VERSION, false );
}
// Prio 30 — läuft nach hp_run_glossar_seed_once (Prio 25), damit alle Glossar-Posts verfügbar sind.
add_action( 'admin_init', 'hp_run_dossier_transhumanismus_seed_once', 30 );

function hp_seed_transhumanismus_dossier(): void {
	$slug  = 'transhumanismus-die-flucht-aus-dem-menschen';
	$title = 'Transhumanismus — die Flucht aus dem Menschen';

	$intro = 'Dieses Dossier dekonstruiert den Transhumanismus als das einflussreichste biopolitische Projekt der Gegenwart: eine säkulare Erlösungsreligion, getragen von Silicon-Valley-Kapital, gespeist aus Todesangst — und konfrontiert mit den Grenzen biologischer Wirklichkeit.';

	$meta_desc = 'Dossier: Transhumanismus als säkulare Erlösungsideologie — Machtanalyse, technologische Realitätsprüfung, psychologische Triebkräfte und philosophische Gegenpositionen.';

	// Kernessay
	$kernessay    = get_page_by_path( 'sterblichkeit-kein-softwarefehler', OBJECT, 'essay' );
	$kernessay_id = $kernessay instanceof WP_Post ? $kernessay->ID : 0;

	// Vollständiger Begriffsapparat — 19 Terme
	$glossar_slugs = [
		'transhumanismus',
		'mind-uploading',
		'enhancement-technologien',
		'biophobie',
		'biophilie',
		'conditio-humana',
		'verkoerperte-kognition',
		'phanomenologie-des-leibes',
		'reduktionismus-methodischer',
		'algorithmische-oeffentlichkeit',
		'kosmotechnik',
		'hartes-problem-des-bewusstseins',
		'determinismus-mechanistischer',
		'pessimismus-philosophischer',
		'terror-management-theorie',
		'posthumanismus',
		'technologische-singularitaet',
		'longtermismus',
		'sympatheia',
	];

	$begriffe_ids = [];
	foreach ( $glossar_slugs as $g_slug ) {
		$term = get_page_by_path( $g_slug, OBJECT, 'glossar' );
		if ( $term instanceof WP_Post ) {
			$begriffe_ids[] = $term->ID;
		}
	}

	$quellen = implode( "\n", [
		'Becker, E. (1973). The Denial of Death. Free Press.',
		'Bostrom, N. & Sandberg, A. (2008). Whole Brain Emulation: A Roadmap. FHI Technical Report.',
		'Habermas, J. (2001). Die Zukunft der menschlichen Natur. Suhrkamp.',
		'Kurzweil, R. (2005). The Singularity Is Near. Viking.',
		'MacAskill, W. (2022). What We Owe the Future. Basic Books.',
		'Merleau-Ponty, M. (1945). Phänomenologie der Wahrnehmung. De Gruyter.',
		'Parfit, D. (1984). Reasons and Persons. Oxford University Press.',
		'Sandel, M. J. (2007). The Case Against Perfection. Harvard University Press.',
		'Williams, B. (1973). The Makropulos Case. In Problems of the Self. Cambridge University Press.',
		'Chetty, R. et al. (2016). The Association Between Income and Life Expectancy. JAMA 315(16).',
		'World Happiness Report 2025 — Wellbeing Research Centre, Oxford.',
		'OpenWorm Project (seit 2011) — openworm.org',
		'Altos Labs — Gründung 2022 mit 3 Mrd. USD (u.a. Jeff Bezos, Yuri Milner).',
		'Future of Humanity Institute, Oxford (2005–2024). Geschlossen April 2024.',
	] );

	$post_content = hp_build_transhumanismus_dossier_content( $kernessay_id );

	$post_args = [
		'post_type'    => 'dossier',
		'post_status'  => 'publish',
		'post_name'    => $slug,
		'post_title'   => $title,
		'post_content' => $post_content,
	];

	$existing = get_page_by_path( $slug, OBJECT, 'dossier' );

	if ( $existing instanceof WP_Post ) {
		$post_id              = $existing->ID;
		$post_args['ID']      = $post_id;
		wp_update_post( $post_args );
	} else {
		$post_id = wp_insert_post( $post_args, true );
		if ( is_wp_error( $post_id ) || ! $post_id ) {
			return;
		}
	}

	update_post_meta( $post_id, '_hp_dossier_intro',        $intro );
	update_post_meta( $post_id, '_hp_meta_description',     $meta_desc );
	update_post_meta( $post_id, '_hp_dossier_kuratiert_von', 'Haşim Üner' );
	update_post_meta( $post_id, '_hp_dossier_version',      '1.0' );
	update_post_meta( $post_id, '_hp_dossier_stand',        current_time( 'Y-m-d' ) );
	update_post_meta( $post_id, '_hp_dossier_quellen',      $quellen );

	if ( $kernessay_id ) {
		update_post_meta( $post_id, '_hp_dossier_leseplan', (string) $kernessay_id );
	}
	if ( ! empty( $begriffe_ids ) ) {
		update_post_meta( $post_id, '_hp_dossier_begriffe', implode( ',', $begriffe_ids ) );
	}

	if ( taxonomy_exists( 'topic' ) ) {
		wp_set_object_terms(
			$post_id,
			[ 'macht-und-ordnung', 'gesellschaft-und-wandel', 'sprache-und-begriff' ],
			'topic',
			false
		);
	}
}

/**
 * Baut den Gutenberg-Block-Content des Dossiers.
 *
 * @param int $kernessay_id Post-ID des Kernessays (0 wenn nicht vorhanden).
 * @return string
 */
function hp_build_transhumanismus_dossier_content( int $kernessay_id ): string {
	$kernessay_link = $kernessay_id
		? '<a href="' . esc_url( get_permalink( $kernessay_id ) ) . '">Sterblichkeit ist kein Softwarefehler</a>'
		: 'Sterblichkeit ist kein Softwarefehler';

	$blocks = [];

	// Leitfrage
	$blocks[] = '<!-- wp:heading -->
<h2 class="wp-block-heading">Leitfrage</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Ist der Transhumanismus eine Wissenschaft oder eine Erlösungsreligion des Siliziumzeitalters?</p>
<!-- /wp:paragraph -->';

	// Arbeitsthese
	$blocks[] = '<!-- wp:heading -->
<h2 class="wp-block-heading">Arbeitsthese</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Der Transhumanismus ist das einflussreichste biopolitische Projekt der Gegenwart. Seine technologischen Kernversprechen — Mind Uploading, Kryokonservierung, Abschaffung des Alterns — scheitern an biophysikalischen und informationstheoretischen Grenzen. Seine treibende Kraft ist nicht wissenschaftliche Evidenz, sondern eine tief sitzende, unbewusste Angst vor Tod und körperlicher Hinfälligkeit.</p>
<!-- /wp:paragraph -->
<!-- wp:paragraph -->
<p>Dahinter steht ein Menschenbild: Der Körper als reparaturanfällige Hardware. Sterblichkeit als behebbarer Softwarefehler. Bewusstsein als extrahierbare Information. Dieses Bild ist keine Prognose — es ist Ideologie. Und sie hat reale Konsequenzen: für die Verteilung von Lebenszeit, für die Würde des Alterns und Sterbens, für die Frage, wer Zugang zu Optimierung hat und wer nicht.</p>
<!-- /wp:paragraph -->';

	// I. Historische Genese
	$blocks[] = '<!-- wp:heading -->
<h2 class="wp-block-heading">I. Historische Genese und Institutionalisierung</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Die intellektuellen Wurzeln reichen bis Dante (<em>trasumanar</em>, Paradiso I) und reichen über J.B.S. Haldane (1923) und Julian Huxley (1957) in die Gegenwart. Die institutionelle Phase begann 1998 mit der World Transhumanist Association (Nick Bostrom, David Pearce) und dem Future of Humanity Institute in Oxford (2005–2024). Das FHI — mitfinanziert von Elon Musk und Open Philanthropy — entwickelte Longtermismus, existenzielle Risikoforschung und KI-Alignment als akademische Kernkonzepte, bevor es im April 2024 geschlossen wurde.</p>
<!-- /wp:paragraph -->
<!-- wp:paragraph -->
<p>Parallel dazu: ein hochgradig kapitalisierter Transformationsbeschleuniger. Peter Thiel, Jeff Bezos, Sam Altman und Yuri Milner haben über fünf Milliarden US-Dollar in Longevity-Unternehmen investiert. Altos Labs (2022, 3 Mrd. USD), Retro Biosciences (Sam Altman), die Enhanced Games — all das ist nicht Randphänomen, sondern Mainstreamprogramm einer finanzstarken Elite.</p>
<!-- /wp:paragraph -->';

	// II. Technologische Grenzen
	$blocks[] = '<!-- wp:heading -->
<h2 class="wp-block-heading">II. Technologische Realitätsprüfung</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Das menschliche Gehirn enthält rund 86 Milliarden Neuronen und etwa 100 Billionen Synapsen. Das OpenWorm-Projekt versucht seit 2011, den Fadenwurm <em>C. elegans</em> mit exakt 302 Neuronen vollständig zu emulieren — ohne bisher das Verhalten des lebendigen Originals zu replizieren. Wer einen Wurm nicht hochladen kann, sollte vom Upload des Menschen schweigen.</p>
<!-- /wp:paragraph -->
<!-- wp:paragraph -->
<p>Kryokonservierung löst das Problem der Eiskristallbildung durch Vitrifikation. Ein Durchbruch (2025): Hippocampus-Schnitte adulter Mäuse zeigten nach Vitrifikation und Erwärmung nahezu physiologische Erholung. Der Transfer auf ganze Säugetiergehirne scheitert jedoch an systemischer CPA-Toxizität und thermischer Rissbildung. Kryonik bleibt spekulativer Vorschuss auf Nanotechnologien, die noch nicht existieren.</p>
<!-- /wp:paragraph -->';

	// III. Psychologische Dimension
	$blocks[] = '<!-- wp:heading -->
<h2 class="wp-block-heading">III. Psychologische Triebkräfte: Todesangst und Terror-Management</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Ernest Beckers Terror-Management-Theorie erklärt die psychologische Struktur: Das Bewusstsein der eigenen Sterblichkeit erzeugt fundamentale, unbewusste Angst. Menschen konstruieren kulturelle Weltbilder als Schutzschild. Mit fortschreitender Säkularisierung verlieren religiöse Unsterblichkeitsversprechen ihre Wirkung — der Transhumanismus füllt das Vakuum.</p>
<!-- /wp:paragraph -->
<!-- wp:paragraph -->
<p>Kryokonservierung wird zur säkularen Auferstehungstechnologie. Mind Uploading zur technologischen Replikation der unsterblichen Seele. Das ist keine Wissenschaft — es ist Theologie in der Sprache der Informatik. Und es hat einen Namen: irrationale biomedizinische Exuberanz (TRIBE-Modell) — die unbewusste Todesangst der Akteure treibt eine systematische Leugnung biophysikalischer Grenzen.</p>
<!-- /wp:paragraph -->';

	// IV. Philosophische Gegenpositionen
	$blocks[] = '<!-- wp:heading -->
<h2 class="wp-block-heading">IV. Philosophische Gegenpositionen</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p><strong>Habermas (Gattungsethik):</strong> Genetische Optimierung zerstört die moralische Symmetrie zwischen Generationen. Das genetisch programmierte Kind kann sich nicht als gleichberechtigten Schöpfer seiner Biografie begreifen — ein wesentlicher Teil seiner Dispositionen ist fremdbestimmt. Diese Asymmetrie ist nicht diskursiv verhandelbar.</p>
<!-- /wp:paragraph -->
<!-- wp:paragraph -->
<p><strong>Sandel (Geschenkhaftigkeit):</strong> Die moralische Integrität menschlicher Beziehungen beruht auf der Anerkennung des „Geschenkcharakters des Lebens". Transhumanistische Perfektionierung erodiert Demut, explodiert Verantwortung ins Unermessliche und zerstört Solidarität — denn wenn Gesundheit nicht mehr als ungleiches Geschenk des Schicksals gilt, schwindet die Verpflichtung zur Umverteilung.</p>
<!-- /wp:paragraph -->
<!-- wp:paragraph -->
<p><strong>Williams (Unsterblichkeit als Stillstand):</strong> Was unsere Wünsche und Entscheidungen mit Bedeutung auflädt, ist ihre Verknüpfung mit einem endlichen Leben. Unendlichkeit ist Stillstand. Erst weil Zeit begrenzt ist, hat sie Gewicht. Eine Palliativmedizin, die würdiges Sterben ermöglicht, ist humaner als die Verlängerung des biologischen Funktionierens um jeden Preis.</p>
<!-- /wp:paragraph -->';

	// V. Soziale Demaskierung
	$blocks[] = '<!-- wp:heading -->
<h2 class="wp-block-heading">V. Die soziale Demaskierung</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Lebenszeit ist heute ungleich verteilt. Die Chetty-Studie (JAMA 2016, 1,4 Mrd. Steuerdatensätze): Zwischen reichstem und ärmstem Prozent der US-Bevölkerung liegt eine Lücke von 15 Jahren Lebenserwartung — nicht durch Biologie, sondern durch Lebensbedingungen. Diese Lücke schließt sich durch Verteilung, nicht durch Technologie.</p>
<!-- /wp:paragraph -->
<!-- wp:paragraph -->
<p>Eine marktförmig organisierte Lebensverlängerung legt sich auf bestehendes Gefälle und macht es steiler. Die USA sind im World Happiness Report 2025 auf Platz 24 gefallen — inmitten von Longevity-Milliarden. Der Transhumanismus hält ein krankes System für eine kranke Existenz. Das ist nicht nur ungenau — es verhindert den Blick auf das, was sich tatsächlich ändern ließe.</p>
<!-- /wp:paragraph -->';

	// Kerntext
	$blocks[] = '<!-- wp:heading -->
<h2 class="wp-block-heading">Kerntext</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>' . $kernessay_link . ' — Essay (22 Min. Lesezeit)</p>
<!-- /wp:paragraph -->';

	return implode( "\n\n", $blocks );
}
