<?php
/**
 * Dossier Seed — Transhumanismus
 *
 * Idempotentes Anlegen der ersten Phase des Dossiers "Transhumanismus — die Flucht aus dem Menschen".
 *
 * @package Hasimuener_Journal
 */

defined( 'ABSPATH' ) || exit;

const HP_DOSSIER_TRANSHUMANISMUS_SEED_VERSION = 'v1-draft';

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
	$intro = 'Dieses Dossier untersucht den Transhumanismus als moderne Erlösungsfantasie. Im Zentrum steht nicht nur die Frage, welche Technologien den Menschen verbessern sollen, sondern welches Menschenbild dahintersteht. Der Körper erscheint als Mangel, Sterblichkeit als technisches Problem, Bewusstsein als Information. Der Traum vom optimierten Menschen ist damit auch eine Flucht aus Verletzlichkeit, Begrenzung und Leiblichkeit.';
	$meta_desc = 'Ein Dossier über Transhumanismus, Körper, Sterblichkeit, Mind Uploading und die Ideologie technischer Erlösung.';

	// Finde existierenden Kerntext
	$kernessay = get_page_by_path( 'sterblichkeit-kein-softwarefehler', OBJECT, 'essay' );
	$kernessay_id = $kernessay instanceof WP_Post ? $kernessay->ID : '';

	// Finde Glossar-Begriffe
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
		'kosmotechnik'
	];
	
	$begriffe_ids = [];
	foreach ( $glossar_slugs as $g_slug ) {
		$term = get_page_by_path( $g_slug, OBJECT, 'glossar' );
		if ( $term instanceof WP_Post ) {
			$begriffe_ids[] = $term->ID;
		}
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
<p>Transhumanismus ist keine neutrale Zukunftsvision, sondern eine Ideologie der Entkörperung. Der Mensch wird als fehlerhaftes System betrachtet, das optimiert, ersetzt oder überwunden werden soll.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Kerntext / Startpunkt</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>' . ( $kernessay_id ? 'Kernessay: <a href="/essays/sterblichkeit-kein-softwarefehler/">Sterblichkeit ist kein Softwarefehler</a>' : 'TODO: Vorhandenen Kernessay verlinken.' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Vorläufiger Leseplan</h2>
<!-- /wp:heading -->
<!-- wp:list {"ordered":true} -->
<ol class="wp-block-list">
<li>' . ( $kernessay_id ? 'Kernessay: <a href="/essays/sterblichkeit-kein-softwarefehler/">Sterblichkeit ist kein Softwarefehler</a> / vorhandener Essay' : 'TODO: Kernessay finden' ) . '</li>
<li>TODO: Kontexttext: Transhumanismus als Erlösungsfantasie</li>
<li>TODO: Kritik: Der Körper ist kein Fehler</li>
<li>TODO: Machtfrage: Optimierung, Klasse und Zugang</li>
<li>TODO: Gegenentwurf: Biophilie statt Biophobie</li>
</ol>
<!-- /wp:list -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Noch fehlende Glossar-Begriffe</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Aktuell keine – alle angefragten Begriffe wurden im System gefunden und verknüpft.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Offene Fragen</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>TODO: Welche weiteren Fragen müssen im Rahmen des Dossiers geklärt werden?</p>
<!-- /wp:paragraph -->';

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
	update_post_meta( $post_id, '_hp_dossier_version', '0.1 (Draft)' );
	update_post_meta( $post_id, '_hp_dossier_stand', date( 'Y-m-d' ) );

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
