<?php
/**
 * Wissensgraph — REST-API + Conditional Asset Loading
 *
 * Stellt den Endpoint /wp-json/hp/v1/graph bereit, der alle
 * Beziehungsdaten (Nodes + Edges) für die D3.js-Visualisierung
 * als JSON liefert. Ergebnisse werden kompiliert in der DB gehalten.
 * Teure Rebuilds laufen über Content-/Topic-Hooks und WP-Cron.
 *
 * Assets (D3.js + graph.js) werden nur auf der Graph-Seite geladen.
 *
 * @package Hasimuener_Journal
 * @since   6.0.0
 */

defined( 'ABSPATH' ) || exit;

/* =========================================
   1. REST-API ENDPOINT
   ========================================= */

/**
 * Registriert den REST-Endpoint /wp-json/hp/v1/graph.
 */
function hp_graph_register_rest_route(): void {
	register_rest_route( 'hp/v1', '/graph', [
		'methods'             => 'GET',
		'callback'            => 'hp_graph_rest_callback',
		'permission_callback' => '__return_true',
	] );
}
add_action( 'rest_api_init', 'hp_graph_register_rest_route' );

/**
 * REST-Callback: Liefert Graph-Daten (cached).
 *
 * @return WP_REST_Response
 */
function hp_graph_rest_callback(): WP_REST_Response {
	$data = hp_graph_get_compiled_data();

	if ( null === $data ) {
		hp_graph_schedule_rebuild();

		return new WP_REST_Response( hp_graph_empty_payload( hp_graph_get_status() ), 200 );
	}

	$data['meta']['cached'] = true;
	$data['meta']['status'] = hp_graph_get_status();

	return new WP_REST_Response( $data, 200 );
}

/* =========================================
   2. GRAPH-DATEN BAUEN
   ========================================= */

/**
 * Baut das komplette Node/Edge-Datenmodell.
 *
 * @return array{nodes: array, edges: array, meta: array}
 */
function hp_graph_build_data(): array {
	$nodes = [];
	$edges = [];

	// --- Posts laden (essay, note, glossar) ---
	$posts = get_posts( [
		'post_type'      => [ 'essay', 'note', 'glossar' ],
		'post_status'    => 'publish',
		'posts_per_page' => -1,
	] );

	// Node-Maps für Edge-Berechnung
	$post_map   = []; // id => WP_Post
	$node_edges = []; // node_id => edge_count (für Begrenzung)

	foreach ( $posts as $post ) {
		$type    = $post->post_type;
		$node_id = $type . '_' . $post->ID;

		$meta = [];
		if ( 'essay' === $type ) {
			$meta['reading_time'] = hp_reading_time( $post->ID );
			$meta['date']         = get_the_date( 'j. F Y', $post );
			$meta['excerpt']      = hp_graph_get_excerpt( $post );
		} elseif ( 'note' === $type ) {
			$meta['reading_time'] = hp_reading_time( $post->ID );
			$meta['date']         = get_the_date( 'j. F Y', $post );
			$meta['excerpt']      = hp_graph_get_excerpt( $post );
		} elseif ( 'glossar' === $type ) {
			$meta['kurz'] = get_post_meta( $post->ID, '_hp_glossar_kurz', true );
		}

		$nodes[ $node_id ] = [
			'id'    => $node_id,
			'label' => get_the_title( $post ),
			'type'  => $type,
			'url'   => wp_make_link_relative( get_permalink( $post ) ),
			'meta'  => $meta,
		];

		$post_map[ $node_id ] = $post;
		$node_edges[ $node_id ] = 0;
	}

	// --- Topics laden ---
	$topics = get_terms( [
		'taxonomy'   => 'topic',
		'hide_empty' => false,
	] );

	if ( ! is_wp_error( $topics ) ) {
		foreach ( $topics as $term ) {
			$node_id = 'topic_' . $term->term_id;

			$nodes[ $node_id ] = [
				'id'    => $node_id,
				'label' => $term->name,
				'type'  => 'topic',
				'url'   => wp_make_link_relative( get_term_link( $term ) ),
				'meta'  => [
					'count'       => (int) $term->count,
					'description' => $term->description,
				],
			];

			$node_edges[ $node_id ] = 0;
		}
	}

	// --- Topics pro Post laden (einmal für membership + shared) ---
	$post_topic_map = []; // node_id => [term_ids]
	foreach ( $post_map as $node_id => $post ) {
		$term_ids = wp_get_object_terms( $post->ID, 'topic', [ 'fields' => 'ids' ] );
		if ( ! is_wp_error( $term_ids ) && ! empty( $term_ids ) ) {
			$post_topic_map[ $node_id ] = $term_ids;
		}
	}

	// --- Edges: topic_membership ---
	foreach ( $post_topic_map as $node_id => $term_ids ) {
		foreach ( $term_ids as $term_id ) {
			$topic_node_id = 'topic_' . $term_id;
			if ( isset( $nodes[ $topic_node_id ] ) ) {
				$edges[] = [
					'source' => $node_id,
					'target' => $topic_node_id,
					'type'   => 'topic_membership',
					'weight' => 2,
				];
				$node_edges[ $node_id ]++;
				$node_edges[ $topic_node_id ]++;
			}
		}
	}

	// --- Edges: shared_topic ---

	$post_node_ids = array_keys( $post_topic_map );
	$shared_seen   = [];
	for ( $i = 0, $len = count( $post_node_ids ); $i < $len; $i++ ) {
		for ( $j = $i + 1; $j < $len; $j++ ) {
			$a = $post_node_ids[ $i ];
			$b = $post_node_ids[ $j ];
			$shared = array_intersect( $post_topic_map[ $a ], $post_topic_map[ $b ] );
			if ( ! empty( $shared ) ) {
				$edge_key = $a . '-' . $b;
				if ( ! isset( $shared_seen[ $edge_key ] ) ) {
					$edges[] = [
						'source' => $a,
						'target' => $b,
						'type'   => 'shared_topic',
						'weight' => count( $shared ),
					];
					$node_edges[ $a ]++;
					$node_edges[ $b ]++;
					$shared_seen[ $edge_key ] = true;
				}
			}
		}
	}

	// --- Edges: glossar_in_content ---
	$glossar_entries = [];
	foreach ( $post_map as $node_id => $post ) {
		if ( 'glossar' !== $post->post_type ) {
			continue;
		}
		$title = get_the_title( $post );
		$patterns = [];
		if ( $title ) {
			$patterns[] = preg_quote( $title, '/' );
		}
		$synonyme = get_post_meta( $post->ID, '_hp_glossar_synonyme', true );
		if ( $synonyme ) {
			foreach ( explode( ',', $synonyme ) as $syn ) {
				$syn = trim( $syn );
				if ( $syn ) {
					$patterns[] = preg_quote( $syn, '/' );
				}
			}
		}
		if ( ! empty( $patterns ) ) {
			$glossar_entries[ $node_id ] = $patterns;
		}
	}

	foreach ( $post_map as $node_id => $post ) {
		if ( 'glossar' === $post->post_type ) {
			continue;
		}
		$content = wp_strip_all_tags( $post->post_content );
		foreach ( $glossar_entries as $glossar_node_id => $patterns ) {
			foreach ( $patterns as $pattern ) {
				if ( preg_match( '/\b' . $pattern . '\b/ui', $content ) ) {
					$edges[] = [
						'source' => $glossar_node_id,
						'target' => $node_id,
						'type'   => 'glossar_in_content',
						'weight' => 3,
					];
					$node_edges[ $glossar_node_id ]++;
					$node_edges[ $node_id ]++;
					break; // Nur einmal pro Glossar-Eintrag/Beitrag
				}
			}
		}
	}

	// --- Begrenzung: Max 200 Nodes (meiste Verbindungen behalten) ---
	if ( count( $nodes ) > 200 ) {
		arsort( $node_edges );
		$keep = array_slice( array_keys( $node_edges ), 0, 200 );
		$keep_set = array_flip( $keep );

		$nodes = array_filter( $nodes, function ( $node ) use ( $keep_set ) {
			return isset( $keep_set[ $node['id'] ] );
		} );

		$edges = array_filter( $edges, function ( $edge ) use ( $keep_set ) {
			return isset( $keep_set[ $edge['source'] ] ) && isset( $keep_set[ $edge['target'] ] );
		} );
	}

	// Array-Keys zurücksetzen für sauberes JSON
	$nodes = array_values( $nodes );
	$edges = array_values( $edges );

	$neighbor_map = [];
	foreach ( $edges as $edge ) {
		$source = (string) ( $edge['source'] ?? '' );
		$target = (string) ( $edge['target'] ?? '' );
		$weight = isset( $edge['weight'] ) ? (int) $edge['weight'] : 1;

		if ( '' === $source || '' === $target ) {
			continue;
		}

		$neighbor_map[ $source ][ $target ] = ( $neighbor_map[ $source ][ $target ] ?? 0 ) + $weight;
		$neighbor_map[ $target ][ $source ] = ( $neighbor_map[ $target ][ $source ] ?? 0 ) + $weight;
	}

	foreach ( $neighbor_map as $node_id => $neighbors ) {
		arsort( $neighbors );
		$neighbor_map[ $node_id ] = array_keys( $neighbors );
	}

	return [
		'nodes'     => $nodes,
		'edges'     => $edges,
		'neighbors' => $neighbor_map,
		'meta'      => [
			'node_count' => count( $nodes ),
			'edge_count' => count( $edges ),
			'generated'  => wp_date( 'c' ),
			'cached'     => false,
		],
	];
}

/**
 * Gibt einen gekürzten Excerpt für einen Post zurück.
 *
 * @param WP_Post $post
 * @return string
 */
function hp_graph_get_excerpt( WP_Post $post ): string {
	if ( has_excerpt( $post->ID ) ) {
		return wp_strip_all_tags( get_the_excerpt( $post ) );
	}
	return wp_trim_words( wp_strip_all_tags( $post->post_content ), 25, ' …' );
}

/* =========================================
   3. CACHE-KOMPILIERUNG
   ========================================= */

/**
 * Liefert den aktuellen Graph-Cache-Status.
 *
 * @return string
 */
function hp_graph_get_status(): string {
	$status = (string) get_option( 'hp_graph_status', 'pending' );
	return in_array( $status, [ 'ready', 'stale', 'pending', 'error' ], true ) ? $status : 'pending';
}

/**
 * Leeres, aber valides Graph-Payload.
 *
 * @param string $status Cache-Status.
 * @return array{nodes: array, edges: array, meta: array}
 */
function hp_graph_empty_payload( string $status = 'pending' ): array {
	$payload = [
		'nodes' => [],
		'edges' => [],
		'meta'  => [
			'node_count' => 0,
			'edge_count' => 0,
			'generated'  => '',
			'cached'     => true,
			'status'     => $status,
			'version'    => (int) get_option( 'hp_graph_version', 0 ),
		],
	];

	$error = (string) get_option( 'hp_graph_last_error', '' );
	if ( 'error' === $status && '' !== $error ) {
		$payload['meta']['error'] = $error;
	}

	return $payload;
}

/**
 * Validiert ein kompiliertes Graph-Payload.
 *
 * @param mixed $data Potenzielles Payload.
 * @return bool
 */
function hp_graph_is_valid_payload( $data ): bool {
	return is_array( $data )
		&& isset( $data['nodes'], $data['edges'], $data['meta'] )
		&& is_array( $data['nodes'] )
		&& is_array( $data['edges'] )
		&& is_array( $data['meta'] );
}

/**
 * Liefert fertig kompiliertes Graph-Payload aus der Datenbank.
 *
 * Fällt nur auf alte Transient-Daten zurück, um bestehende Installationen
 * ohne synchronen Rebuild zu migrieren.
 *
 * @return array|null
 */
function hp_graph_get_compiled_data(): ?array {
	$data = get_option( 'hp_graph_payload', null );

	if ( hp_graph_is_valid_payload( $data ) ) {
		return $data;
	}

	$legacy_key = 'hp_graph_data_v' . (int) get_option( 'hp_glossar_version', 0 );
	$legacy    = get_transient( $legacy_key );

	if ( hp_graph_is_valid_payload( $legacy ) ) {
		hp_graph_store_compiled_data( $legacy );
		return $legacy;
	}

	return null;
}

/**
 * Speichert kompiliertes Graph-Payload persistent, aber nicht autoloaded.
 *
 * @param array $data Graph-Payload.
 */
function hp_graph_store_compiled_data( array $data ): void {
	$data['meta']['cached']  = false;
	$data['meta']['status']  = 'ready';
	$data['meta']['version'] = (int) get_option( 'hp_graph_version', 0 );

	update_option( 'hp_graph_payload', $data, false );
	update_option( 'hp_graph_status', 'ready', false );
	delete_option( 'hp_graph_last_error' );
}

/**
 * Plant einen Graph-Rebuild, falls nicht schon einer ansteht.
 */
function hp_graph_schedule_rebuild(): void {
	if ( ! wp_next_scheduled( 'hp_graph_rebuild_event' ) ) {
		wp_schedule_single_event( time() + 15, 'hp_graph_rebuild_event' );
	}
}

/**
 * Kompiliert den Graph im Hintergrund neu.
 */
function hp_graph_rebuild_compiled_data(): void {
	try {
		$data = hp_graph_build_data();
		hp_graph_store_compiled_data( $data );
	} catch ( \Throwable $e ) {
		update_option( 'hp_graph_status', 'error', false );
		update_option( 'hp_graph_last_error', $e->getMessage(), false );
	}
}
add_action( 'hp_graph_rebuild_event', 'hp_graph_rebuild_compiled_data' );

/**
 * Markiert den Graph als stale und plant den asynchronen Rebuild.
 */
function hp_graph_mark_stale_and_schedule(): void {
	$new_version = (int) get_option( 'hp_graph_version', 0 ) + 1;
	update_option( 'hp_graph_version', $new_version, false );

	$status = hp_graph_get_compiled_data() ? 'stale' : 'pending';
	update_option( 'hp_graph_status', $status, false );

	hp_graph_schedule_rebuild();
}

/* =========================================
   4. CACHE INVALIDIERUNG
   ========================================= */

/**
 * Plant einen Graph-Rebuild bei Änderungen an
 * Essays, Notizen oder Glossar-Einträgen.
 *
 * @param int $post_id
 */
function hp_graph_flush_cache( int $post_id ): void {
	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}

	$type = get_post_type( $post_id );
	if ( ! in_array( $type, [ 'essay', 'note', 'glossar' ], true ) ) {
		return;
	}

	hp_graph_mark_stale_and_schedule();
}
add_action( 'save_post_essay', 'hp_graph_flush_cache' );
add_action( 'save_post_note', 'hp_graph_flush_cache' );
add_action( 'save_post_glossar', 'hp_graph_flush_cache' );
add_action( 'delete_post', 'hp_graph_flush_cache' );

/**
 * Invalidiert Graph-Cache bei Topic-Änderungen.
 *
 * @param int $term_id
 */
function hp_graph_flush_cache_on_topic( int $term_id ): void {
	hp_graph_mark_stale_and_schedule();
}
add_action( 'edited_topic', 'hp_graph_flush_cache_on_topic' );
add_action( 'created_topic', 'hp_graph_flush_cache_on_topic' );
add_action( 'delete_topic', 'hp_graph_flush_cache_on_topic' );

/* =========================================
   5. CONDITIONAL ASSET LOADING
   ========================================= */

/**
 * Lädt D3.js und graph.js nur auf der Graph-Seite.
 * Graph-Daten werden direkt als Inline-JSON eingebettet —
 * kein REST-Aufruf nötig.
 *
 * Seit 4.1.0: Custom-D3-Bundle (d3-custom.min.js, ~71 KB)
 * statt vollem D3 (d3.min.js, ~273 KB). Nur d3-selection,
 * d3-scale, d3-zoom, d3-drag, d3-force enthalten.
 */
function hp_graph_enqueue_assets(): void {
	if ( ! is_page( 'wissensgraph' ) ) {
		return;
	}

	$theme_version = wp_get_theme()->get( 'Version' );
	$d3_path       = get_stylesheet_directory() . '/assets/js/d3-custom.min.js';
	$graph_path    = get_stylesheet_directory() . '/assets/js/graph.js';
	$graph_css_path = get_stylesheet_directory() . '/assets/css/pages/wissensgraph.css';
	$d3_version    = file_exists( $d3_path ) ? (string) filemtime( $d3_path ) : $theme_version;
	$graph_version = file_exists( $graph_path ) ? (string) filemtime( $graph_path ) : $theme_version;
	$graph_css_version = file_exists( $graph_css_path ) ? (string) filemtime( $graph_css_path ) : $theme_version;

	wp_enqueue_style(
		'hp-graph',
		get_stylesheet_directory_uri() . '/assets/css/pages/wissensgraph.css',
		[ 'hp-journal-style' ],
		$graph_css_version
	);

	// D3.js Custom-Bundle (nur genutzte Module) lokal aus dem Theme laden
	wp_enqueue_script(
		'hp-d3',
		get_stylesheet_directory_uri() . '/assets/js/d3-custom.min.js',
		[],
		$d3_version,
		[
			'strategy'  => 'defer',
			'in_footer' => true,
		]
	);

	// Graph JS
	wp_enqueue_script(
		'hp-graph-js',
		get_stylesheet_directory_uri() . '/assets/js/graph.js',
		[ 'hp-d3' ],
		$graph_version,
		[
			'strategy'  => 'defer',
			'in_footer' => true,
		]
	);

	wp_localize_script( 'hp-graph-js', 'hpGraph', [
		'restUrl' => esc_url_raw( rest_url( 'hp/v1/graph' ) ),
	] );
}
add_action( 'wp_enqueue_scripts', 'hp_graph_enqueue_assets' );
