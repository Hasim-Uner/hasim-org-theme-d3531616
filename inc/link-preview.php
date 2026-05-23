<?php
/**
 * Link-Preview — Hover-Tooltip für interne Links
 *
 * Stellt den Endpoint /wp-json/hp/v1/link-preview bereit, der für
 * eine gegebene URL einen kompakten Preview-Datensatz liefert:
 * Titel, Kurzbeschreibung, Post-Typ-Label und Zielsystem-URL.
 *
 * Wird vom Frontend-Script `link-preview.js` beim Hover über interne
 * Links abgefragt — analog zum bestehenden Glossar-Tooltip.
 *
 * @package Hasimuener_Journal
 * @since   5.2.0
 */

defined( 'ABSPATH' ) || exit;

/* =========================================
   1. REST-API ENDPOINT
   ========================================= */

/**
 * Registriert /wp-json/hp/v1/link-preview.
 */
function hp_link_preview_register_route(): void {
	register_rest_route( 'hp/v1', '/link-preview', [
		'methods'             => 'GET',
		'callback'            => 'hp_link_preview_rest_callback',
		'permission_callback' => '__return_true',
		'args'                => [
			'url' => [
				'required'          => true,
				'type'              => 'string',
				'sanitize_callback' => 'esc_url_raw',
			],
		],
	] );
}
add_action( 'rest_api_init', 'hp_link_preview_register_route' );

/**
 * REST-Callback: Liefert Preview-Daten zu einer URL.
 *
 * @param WP_REST_Request $request
 * @return WP_REST_Response
 */
function hp_link_preview_rest_callback( $request ): WP_REST_Response {
	$url = (string) $request->get_param( 'url' );

	if ( '' === $url ) {
		return new WP_REST_Response( [ 'error' => 'missing_url' ], 400 );
	}

	$post_id = url_to_postid( $url );

	if ( ! $post_id ) {
		return new WP_REST_Response( [ 'error' => 'not_found' ], 404 );
	}

	$cache_key = 'hp_lp_' . $post_id . '_v' . (int) get_option( 'hp_glossar_version', 0 );
	$cached    = get_transient( $cache_key );
	if ( false !== $cached ) {
		return new WP_REST_Response( $cached, 200 );
	}

	$post = get_post( $post_id );
	if ( ! $post || 'publish' !== $post->post_status ) {
		return new WP_REST_Response( [ 'error' => 'not_published' ], 404 );
	}

	$data = hp_link_preview_build_payload( $post );

	set_transient( $cache_key, $data, HOUR_IN_SECONDS * 6 );

	return new WP_REST_Response( $data, 200 );
}

/* =========================================
   2. PAYLOAD-AUFBAU
   ========================================= */

/**
 * Liefert das Preview-Payload für einen Beitrag.
 *
 * Glossar-Einträge nutzen die Kurzdefinition,
 * Dossiers ihren Intro-Text, sonst Excerpt / gekürzter Content.
 *
 * @param \WP_Post $post
 * @return array{id:int,type:string,type_label:string,title:string,excerpt:string,url:string,meta:string}
 */
function hp_link_preview_build_payload( \WP_Post $post ): array {
	$type   = $post->post_type;
	$labels = [
		'essay'   => 'Essay',
		'note'    => 'Notiz',
		'glossar' => 'Glossar',
		'dossier' => 'Dossier',
		'page'    => 'Seite',
		'post'    => 'Beitrag',
	];
	$type_label = $labels[ $type ] ?? ucfirst( $type );

	$excerpt = '';
	$meta    = '';

	switch ( $type ) {
		case 'glossar':
			$excerpt = (string) get_post_meta( $post->ID, '_hp_glossar_kurz', true );
			break;

		case 'dossier':
			$intro = (string) get_post_meta( $post->ID, '_hp_dossier_intro', true );
			$excerpt = $intro !== '' ? $intro : $post->post_excerpt;
			break;

		default:
			$excerpt = (string) $post->post_excerpt;
			if ( '' === trim( $excerpt ) ) {
				$excerpt = wp_strip_all_tags( strip_shortcodes( $post->post_content ) );
				$excerpt = wp_trim_words( $excerpt, 32, ' …' );
			}
	}

	$excerpt = wp_strip_all_tags( $excerpt );
	$excerpt = trim( preg_replace( '/\s+/', ' ', $excerpt ) );

	if ( in_array( $type, [ 'essay', 'note', 'post' ], true ) && function_exists( 'hp_reading_time' ) ) {
		$meta = hp_reading_time( $post->ID );
	}

	return [
		'id'         => (int) $post->ID,
		'type'       => $type,
		'type_label' => $type_label,
		'title'      => get_the_title( $post ),
		'excerpt'    => $excerpt,
		'url'        => get_permalink( $post ),
		'meta'       => $meta,
	];
}

/* =========================================
   3. CACHE INVALIDIERUNG
   ========================================= */

/**
 * Invalidiert den Preview-Cache eines Beitrags, wenn er
 * aktualisiert oder gelöscht wird.
 */
function hp_link_preview_flush( int $post_id ): void {
	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}
	$version = (int) get_option( 'hp_glossar_version', 0 );
	delete_transient( 'hp_lp_' . $post_id . '_v' . $version );
}
add_action( 'save_post',   'hp_link_preview_flush' );
add_action( 'delete_post', 'hp_link_preview_flush' );
