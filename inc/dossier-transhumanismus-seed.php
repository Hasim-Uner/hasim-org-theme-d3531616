<?php
/**
 * Dossier Seed — Transhumanismus
 *
 * Idempotentes Anlegen der zweiten Phase des Dossiers "Transhumanismus — die Flucht aus dem Menschen".
 *
 * @package Hasimuener_Journal
 */

defined( 'ABSPATH' ) || exit;

const HP_DOSSIER_TRANSHUMANISMUS_SEED_VERSION = 'v2-draft';

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
add_action( 'admin_init', 'hp_run_dossier_transhumanismus_seed_once', 25 );

function hp_seed_transhumanismus_dossier(): void {
	$slug = 'transhumanismus-die-flucht-aus-dem-menschen';
	$title = 'Transhumanismus — die Flucht aus dem Menschen';
	
	// Redaktionell geschärftes Intro
	$intro = 'Dieses Dossier betrachtet den Transhumanismus als säkulare Erlösungsfantasie. Im Zentrum steht weniger die technische Machbarkeit von Implantaten oder Bewusstseinstransfers, sondern das Menschenbild, das diesen Visionen zugrunde liegt. Der Körper erscheint hier als reparaturanfällige Hardware, Sterblichkeit als behebbarer Softwarefehler und Bewusstsein als bloße Information. Der Traum vom grenzenlos optimierten Menschen erweist sich bei genauerem Hinsehen als eine tiefe Flucht aus der eigenen Verletzlichkeit, Begrenzung und Leiblichkeit.';
	
	$meta_desc = 'Ein Dossier über Transhumanismus, Körper, Sterblichkeit, Mind Uploading und die Ideologie technischer Erlösung.';

	// Finde existierenden Kerntext
	$kernessay = get_page_by_path( 'sterblichkeit-kein-softwarefehler', OBJECT, 'essay' );
	$kernessay_id = $kernessay instanceof WP_Post ? $kernessay->ID : '';

	// Glossar-Begriffe prüfen
	$glossar_slugs = [
		'transhumanismus'                => 'Transhumanismus',
		'mind-uploading'                 => 'Mind Uploading',
		'enhancement-technologien'       => 'Enhancement-Technologien',
		'biophobie'                      => 'Biophobie',
		'biophilie'                      => 'Biophilie',
		'conditio-humana'                => 'Conditio humana',
		'verkoerperte-kognition'         => 'Verkörperte Kognition',
		'phanomenologie-des-leibes'      => 'Phänomenologie des Leibes',
		'reduktionismus-methodischer'    => 'Reduktionismus',
		'algorithmische-oeffentlichkeit' => 'Algorithmische Öffentlichkeit',
		'kosmotechnik'                   => 'Kosmotechnik',
		'technologische-singularitaet'   => 'Technologische Singularität',
		'posthumanismus'                 => 'Posthumanismus',
		'kybernetik'                     => 'Kybernetik'
	];
	
	$begriffe_ids = [];
	$begriffe_list_html = '';
	$missing_begriffe_html = '';
	
	foreach ( $glossar_slugs as $g_slug => $g_name ) {
		$term = get_page_by_path( $g_slug, OBJECT, 'glossar' );
		if ( $term instanceof WP_Post ) {
			$begriffe_ids[] = $term->ID;
			$begriffe_list_html .= '<li><a href="' . esc_url( get_permalink( $term ) ) . '">' . esc_html( $term->post_title ) . '</a></li>';
		} else {
			$missing_begriffe_html .= '<li>' . esc_html( $g_name ) . '</li>';
		}
	}
	
	if ( empty( $begriffe_list_html ) ) {
		$begriffe_list_html = '<li>Keine Begriffe gefunden.</li>';
	}
	if ( empty( $missing_begriffe_html ) ) {
		$missing_begriffe_html = '<li>Alle Begriffe sind vorhanden.</li>';
	}

	$post_content = '<!-- wp:heading -->
<h2 class="wp-block-heading">Leitfrage</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Warum träumt der moderne Mensch davon, den Körper, die Sterblichkeit und die eigene Begrenztheit technisch zu überwinden?</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Arbeitsthese</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Transhumanismus ist keine neutrale Zukunftsvision, sondern eine Ideologie der Entkörperung. Der Mensch wird als fehlerhaftes System betrachtet, das durch Technologie optimiert, ersetzt oder überwunden werden soll. Sterblichkeit und Körperlichkeit gelten als Defekte, Bewusstsein als extrahierbare Daten.</p>
<!-- /wp:paragraph -->
<!-- wp:paragraph -->
<p>Dabei stellt sich unweigerlich die Machtfrage: Wem gehören die Mittel zur Optimierung, und wer wird dadurch an den Rand gedrängt? Es geht um Kapital, Zugang und die Visionen einer technologischen Elite. Als Gegenentwurf dazu steht eine Biophilie, die unsere physische Existenz, Begrenztheit und Verletzlichkeit nicht als Fehler, sondern als Kern unserer Menschlichkeit begreift.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Kerntext / Startpunkt</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>' . ( $kernessay_id ? 'Kernessay: <a href="/essays/sterblichkeit-kein-softwarefehler/">Sterblichkeit ist kein Softwarefehler</a>' : 'TODO: Vorhandenen Kernessay verlinken.' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Leseplan</h2>
<!-- /wp:heading -->
<!-- wp:list {"ordered":true} -->
<ol class="wp-block-list">
<li><strong>Einstieg:</strong> Grundkonflikt verstehen.<br>' . ( $kernessay_id ? 'Kernessay: <a href="/essays/sterblichkeit-kein-softwarefehler/">Sterblichkeit ist kein Softwarefehler</a>' : 'TODO: Kernessay finden' ) . '</li>
<li><strong>Begriffliche Grundlage:</strong> <a href="/glossar/transhumanismus/">Transhumanismus</a>, <a href="/glossar/mind-uploading/">Mind Uploading</a> und Enhancement einordnen. (TODO: Texte zuordnen)</li>
<li><strong>Körper und Leiblichkeit:</strong> Verstehen, warum der Körper im transhumanistischen Denken als Mangel erscheint. (TODO: Texte zuordnen)</li>
<li><strong>Bewusstsein und Reduktionismus:</strong> Kritik an der Idee, Bewusstsein als Information oder Datei zu verstehen. (TODO: Texte zuordnen)</li>
<li><strong>Macht und Klasse:</strong> Enhancement nicht nur technisch, sondern politisch und ökonomisch betrachten. (TODO: Texte zuordnen)</li>
<li><strong>Gegenentwurf:</strong> <a href="/glossar/biophilie/">Biophilie</a>, Verkörperung und Begrenzung als Gegenposition sichtbar machen. (TODO: Texte zuordnen)</li>
</ol>
<!-- /wp:list -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Begriffsapparat</h2>
<!-- /wp:heading -->
<!-- wp:list -->
<ul class="wp-block-list">
' . $begriffe_list_html . '
</ul>
<!-- /wp:list -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Fehlende Glossar-Begriffe (TODO)</h2>
<!-- /wp:heading -->
<!-- wp:list -->
<ul class="wp-block-list">
' . $missing_begriffe_html . '
</ul>
<!-- /wp:list -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Quellen / Weiterführend (TODOs)</h2>
<!-- /wp:heading -->
<!-- wp:list -->
<ul class="wp-block-list">
<li>Primärtexte des Transhumanismus</li>
<li>Kritik an Mind Uploading</li>
<li>Verkörperte Kognition</li>
<li>Phänomenologie des Leibes</li>
<li>Technikphilosophie</li>
<li>Silicon Valley / Ideologie / Kapital</li>
<li>Biophilie / Ökologie / Lebendigkeit</li>
</ul>
<!-- /wp:list -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Redaktionelle TODO-Liste</h2>
<!-- /wp:heading -->
<!-- wp:list -->
<ul class="wp-block-list">
<li>Kernessay final prüfen</li>
<li>Fehlende Glossarbegriffe anlegen</li>
<li>Quellen finalisieren</li>
<li>Leseplan redaktionell prüfen</li>
<li>Ggf. Hero-Bild / Visual bestimmen</li>
<li>SEO final prüfen</li>
<li>Phase 3 QA + Livegang durchführen</li>
</ul>
<!-- /wp:list -->';

	$post_args = [
		'post_type'    => 'dossier',
		'post_status'  => 'draft',
		'post_name'    => $slug,
		'post_title'   => $title,
		'post_content' => $post_content,
	];

	$existing = get_page_by_path( $slug, OBJECT, 'dossier' );
	
	if ( $existing instanceof WP_Post ) {
		$post_id = $existing->ID;
		$post_args['ID'] = $post_id;
		wp_update_post( $post_args );
	} else {
		$post_id = wp_insert_post( $post_args, true );
		if ( is_wp_error( $post_id ) || ! $post_id ) {
			return;
		}
	}

	// Meta Felder aktualisieren
	update_post_meta( $post_id, '_hp_dossier_intro', $intro );
	update_post_meta( $post_id, '_hp_meta_description', $meta_desc );
	update_post_meta( $post_id, '_hp_dossier_version', '0.2 (Draft)' );
	update_post_meta( $post_id, '_hp_dossier_stand', current_time( 'Y-m-d' ) );

	if ( $kernessay_id ) {
		update_post_meta( $post_id, '_hp_dossier_leseplan', (string) $kernessay_id );
	}
	if ( ! empty( $begriffe_ids ) ) {
		update_post_meta( $post_id, '_hp_dossier_begriffe', implode( ',', $begriffe_ids ) );
	}

	if ( taxonomy_exists( 'topic' ) ) {
		wp_set_object_terms( $post_id, [ 'Technologie', 'Philosophie', 'Gesellschaft' ], 'topic', false );
	}
}
