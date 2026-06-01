<?php
/**
 * Hasimuener Journal — Votes REST API
 *
 * REST-API-Endpoint für Like/Dislike-Voting-System.
 * Verarbeitet AJAX-Anfragen für Vote-Operationen.
 *
 * @package Hasimuener_Journal
 * @version 4.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registriert REST-Route für Vote-Operationen
 */
function hp_votes_register_rest_route(): void {
	register_rest_route(
		'hasim-org/v1',
		'/vote',
		[
			'methods'             => 'POST',
			'callback'            => 'hp_votes_rest_callback',
			'permission_callback' => '__return_true',
			'args'                => [
				'post_id' => [
					'required'          => true,
					'validate_callback' => function( $param ) {
						return is_numeric( $param ) && $param > 0;
					},
					'sanitize_callback' => 'absint',
				],
				'vote_type' => [
					'required'          => true,
					'validate_callback' => function( $param ) {
						return in_array( $param, [ 'like', 'dislike' ], true );
					},
					'sanitize_callback' => 'sanitize_text_field',
				],
				'nonce' => [
					'required'          => true,
					'validate_callback' => function( $param ) {
						return ! empty( $param );
					},
					'sanitize_callback' => 'sanitize_text_field',
				],
			],
		]
	);
}
add_action( 'rest_api_init', 'hp_votes_register_rest_route' );

/**
 * REST-API Callback für Vote-Operationen
 */
function hp_votes_rest_callback( WP_REST_Request $request ): WP_REST_Response {
	$post_id   = $request->get_param( 'post_id' );
	$vote_type = $request->get_param( 'vote_type' );
	$nonce     = $request->get_param( 'nonce' );

	// Nonce-Validierung
	if ( ! wp_verify_nonce( $nonce, 'hp_vote_nonce' ) ) {
		return new WP_REST_Response(
			[
				'success' => false,
				'message' => 'Ungültige Nonce-Validierung',
			],
			403
		);
	}

	// Post-Existenz prüfen
	$post = get_post( $post_id );
	if ( ! $post ) {
		return new WP_REST_Response(
			[
				'success' => false,
				'message' => 'Beitrag nicht gefunden',
			],
			404
		);
	}

	// Post-Type-Validierung (nur essay und note)
	if ( ! in_array( $post->post_type, [ 'essay', 'note' ], true ) ) {
		return new WP_REST_Response(
			[
				'success' => false,
				'message' => 'Voting nur für Essays und Notizen verfügbar',
			],
			400
		);
	}

	// Vote verarbeiten
	$result = hp_process_vote( $post_id, $vote_type );

	if ( is_wp_error( $result ) ) {
		return new WP_REST_Response(
			[
				'success' => false,
				'message' => $result->get_error_message(),
			],
			400
		);
	}

	// Aktualisierte Vote-Zahlen zurückgeben
	$vote_counts = hp_get_vote_counts( $post_id );

	return new WP_REST_Response(
		[
			'success'    => true,
			'message'    => 'Vote erfolgreich verarbeitet',
			'vote_type'  => $vote_type,
			'likes'      => $vote_counts['likes'],
			'dislikes'   => $vote_counts['dislikes'],
			'user_vote'  => hp_get_user_vote( $post_id ),
		],
		200
	);
}

/**
 * Enqueue Voting-Assets
 */
function hp_votes_enqueue_assets(): void {
	if ( is_singular( [ 'essay', 'note' ] ) ) {
		wp_enqueue_script(
			'hasim-org-votes',
			get_stylesheet_directory_uri() . '/assets/js/votes.js',
			[],
			hp_asset_version( 'assets/js/votes.js' ),
			[
				'strategy'  => 'defer',
				'in_footer' => true,
			]
		);

		wp_localize_script(
			'hasim-org-votes',
			'hasimOrgVotes',
			[
				'ajax_url' => rest_url( 'hasim-org/v1/vote' ),
				'nonce'    => wp_create_nonce( 'hp_vote_nonce' ),
			]
		);

		wp_enqueue_style(
			'hasim-org-votes',
			get_stylesheet_directory_uri() . '/assets/css/votes.css',
			[],
			hp_asset_version( 'assets/css/votes.css' )
		);
	}
}
add_action( 'wp_enqueue_scripts', 'hp_votes_enqueue_assets' );
