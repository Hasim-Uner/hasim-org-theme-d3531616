<?php
/**
 * SEO Cockpit Koko Analytics integration.
 *
 * @package Hasimuener_Journal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Determine whether an array is a list without requiring PHP 8.1.
 *
 * @param mixed $value Candidate value.
 * @return bool
 */
function nexus_is_seo_cockpit_list( $value ) {
	if ( ! is_array( $value ) ) {
		return false;
	}

	if ( function_exists( 'array_is_list' ) ) {
		return array_is_list( $value );
	}

	return array_keys( $value ) === range( 0, count( $value ) - 1 );
}

/**
 * Return one Koko REST route path.
 *
 * @param string $endpoint Endpoint key.
 * @return string
 */
function nexus_get_seo_cockpit_koko_route( $endpoint ) {
	$routes = [
		'totals' => '/koko-analytics/v1/totals',
		'stats'  => '/koko-analytics/v1/stats',
		'posts'  => '/koko-analytics/v1/posts',
	];

	return $routes[ sanitize_key( (string) $endpoint ) ] ?? '';
}

/**
 * Determine whether one Koko REST route is available.
 *
 * @param string $endpoint Endpoint key.
 * @return bool
 */
function nexus_is_seo_cockpit_koko_route_available( $endpoint ) {
	$route = nexus_get_seo_cockpit_koko_route( $endpoint );

	if ( '' === $route || ! function_exists( 'rest_get_server' ) ) {
		return false;
	}

	$routes = rest_get_server()->get_routes();

	return isset( $routes[ $route ] );
}

/**
 * Return a compact Koko Analytics status payload.
 *
 * @return array<string, mixed>
 */
function nexus_get_koko_analytics_status() {
	if ( ! function_exists( 'is_plugin_active' ) && file_exists( ABSPATH . 'wp-admin/includes/plugin.php' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	$plugin_file    = 'koko-analytics/koko-analytics.php';
	$is_active      = function_exists( 'is_plugin_active' ) ? is_plugin_active( $plugin_file ) : false;
	$is_present     = defined( 'WP_PLUGIN_DIR' ) ? file_exists( WP_PLUGIN_DIR . '/' . $plugin_file ) : false;
	$available      = $is_active && nexus_is_seo_cockpit_koko_route_available( 'totals' );
	$available_text = $available ? 'Koko ist aktiv und als Traffic-Kontext im Cockpit verfügbar.' : '';

	if ( $available ) {
		$label = 'Aktiv und mit Cockpit-Kontext verbunden';
	} elseif ( $is_active ) {
		$label = 'Aktiv, aber die REST-Datenroute ist nicht verfügbar';
	} elseif ( $is_present ) {
		$label = 'Installiert, aber nicht aktiv';
	} else {
		$label = 'Noch nicht installiert';
	}

	return [
		'plugin_file'   => $plugin_file,
		'installed'     => $is_present,
		'active'        => $is_active,
		'rest_available' => $available,
		'endpoints'     => [
			'totals' => nexus_is_seo_cockpit_koko_route_available( 'totals' ),
			'stats'  => nexus_is_seo_cockpit_koko_route_available( 'stats' ),
			'posts'  => nexus_is_seo_cockpit_koko_route_available( 'posts' ),
		],
		'label'         => $label,
		'note'          => $available_text,
	];
}

/**
 * Execute one internal Koko REST request.
 *
 * @param string               $endpoint Endpoint key.
 * @param array<string, mixed> $params   Query params.
 * @return mixed|WP_Error
 */
function nexus_seo_cockpit_koko_request( $endpoint, $params = [] ) {
	$status = nexus_get_koko_analytics_status();
	$route  = nexus_get_seo_cockpit_koko_route( $endpoint );

	if ( empty( $status['active'] ) ) {
		return new WP_Error( 'nexus_seo_koko_inactive', 'Koko Analytics ist nicht aktiv.' );
	}

	if ( empty( $status['endpoints'][ sanitize_key( (string) $endpoint ) ] ) || '' === $route ) {
		return new WP_Error( 'nexus_seo_koko_route_missing', 'Die benötigte Koko-REST-Route ist nicht verfügbar.' );
	}

	if ( ! function_exists( 'rest_do_request' ) ) {
		return new WP_Error( 'nexus_seo_koko_rest_unavailable', 'Der WordPress-REST-Server ist nicht verfügbar.' );
	}

	$request = new WP_REST_Request( 'GET', $route );
	$request->set_query_params(
		array_filter(
			(array) $params,
			static function ( $value ) {
				return null !== $value && '' !== $value;
			}
		)
	);

	$response = rest_do_request( $request );
	if ( is_wp_error( $response ) ) {
		return $response;
	}

	$status_code = (int) $response->get_status();
	$data        = $response->get_data();

	if ( $status_code < 200 || $status_code >= 300 ) {
		$message = is_array( $data ) && ! empty( $data['message'] ) ? (string) $data['message'] : 'Koko-Daten konnten nicht geladen werden.';

		return new WP_Error(
			'nexus_seo_koko_request_failed',
			$message,
			[
				'status' => $status_code,
				'route'  => $route,
				'data'   => $data,
			]
		);
	}

	return $data;
}

/**
 * Return one number from a Koko payload by candidate keys.
 *
 * @param array<string, mixed> $payload Payload.
 * @param array<int, string>   $keys    Candidate keys.
 * @return float
 */
function nexus_get_seo_cockpit_koko_number( $payload, $keys ) {
	foreach ( (array) $keys as $key ) {
		if ( isset( $payload[ $key ] ) && is_numeric( $payload[ $key ] ) ) {
			return (float) $payload[ $key ];
		}
	}

	return 0.0;
}

/**
 * Normalize one Koko totals payload.
 *
 * @param mixed $payload Raw payload.
 * @return array<string, float|int>
 */
function nexus_normalize_seo_cockpit_koko_totals( $payload ) {
	$candidates = [];

	if ( is_array( $payload ) ) {
		$candidates[] = $payload;

		foreach ( [ 'totals', 'data', 'summary' ] as $nested_key ) {
			if ( isset( $payload[ $nested_key ] ) && is_array( $payload[ $nested_key ] ) ) {
				$candidates[] = $payload[ $nested_key ];
			}
		}
	}

	foreach ( $candidates as $candidate ) {
		$visitors  = nexus_get_seo_cockpit_koko_number( $candidate, [ 'visitors', 'unique_visitors', 'visits' ] );
		$pageviews = nexus_get_seo_cockpit_koko_number( $candidate, [ 'pageviews', 'page_views', 'views' ] );

		if ( $visitors > 0 || $pageviews > 0 ) {
			return [
				'visitors'  => $visitors,
				'pageviews' => $pageviews,
				'pages'     => max( 0, absint( $candidate['pages'] ?? $candidate['entries'] ?? 0 ) ),
			];
		}
	}

	if ( nexus_is_seo_cockpit_list( $payload ) ) {
		$totals = [
			'visitors'  => 0.0,
			'pageviews' => 0.0,
			'pages'     => count( $payload ),
		];

		foreach ( $payload as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}

			$totals['visitors']  += nexus_get_seo_cockpit_koko_number( $row, [ 'visitors', 'unique_visitors', 'visits' ] );
			$totals['pageviews'] += nexus_get_seo_cockpit_koko_number( $row, [ 'pageviews', 'page_views', 'views' ] );
		}

		return $totals;
	}

	return [
		'visitors'  => 0.0,
		'pageviews' => 0.0,
		'pages'     => 0,
	];
}

/**
 * Normalize one Koko date series.
 *
 * @param string $start   Start date.
 * @param string $end     End date.
 * @param mixed  $payload Raw payload.
 * @return array<int, array<string, mixed>>
 */
function nexus_normalize_seo_cockpit_koko_date_series( $start, $end, $payload ) {
	$rows = [];

	if ( is_array( $payload ) ) {
		if ( nexus_is_seo_cockpit_list( $payload ) ) {
			$rows = $payload;
		} else {
			foreach ( [ 'rows', 'data', 'stats' ] as $nested_key ) {
				if ( isset( $payload[ $nested_key ] ) && is_array( $payload[ $nested_key ] ) ) {
					$rows = $payload[ $nested_key ];
					break;
				}
			}
		}
	}

	$indexed = [];

	foreach ( (array) $rows as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}

		$date = (string) ( $row['date'] ?? $row['day'] ?? $row['label'] ?? '' );
		if ( '' === $date ) {
			continue;
		}

		$indexed[ $date ] = [
			'date'      => $date,
			'visitors'  => nexus_get_seo_cockpit_koko_number( $row, [ 'visitors', 'unique_visitors', 'visits' ] ),
			'pageviews' => nexus_get_seo_cockpit_koko_number( $row, [ 'pageviews', 'page_views', 'views' ] ),
			'has_data'  => true,
		];
	}

	$series = [];
	$cursor = strtotime( $start . ' 00:00:00' );
	$end_ts = strtotime( $end . ' 00:00:00' );

	while ( false !== $cursor && false !== $end_ts && $cursor <= $end_ts ) {
		$date = gmdate( 'Y-m-d', $cursor );
		$series[] = $indexed[ $date ] ?? [
			'date'      => $date,
			'visitors'  => 0.0,
			'pageviews' => 0.0,
			'has_data'  => false,
		];

		$cursor += DAY_IN_SECONDS;
	}

	return $series;
}

/**
 * Normalize one Koko top-posts payload.
 *
 * @param mixed $payload Raw payload.
 * @param int   $limit   Max items.
 * @return array<int, array<string, mixed>>
 */
function nexus_normalize_seo_cockpit_koko_posts( $payload, $limit = 10 ) {
	$rows = [];

	if ( is_array( $payload ) ) {
		if ( nexus_is_seo_cockpit_list( $payload ) ) {
			$rows = $payload;
		} else {
			foreach ( [ 'posts', 'rows', 'data' ] as $nested_key ) {
				if ( isset( $payload[ $nested_key ] ) && is_array( $payload[ $nested_key ] ) ) {
					$rows = $payload[ $nested_key ];
					break;
				}
			}
		}
	}

	$normalized = [];

	foreach ( (array) $rows as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}

		$post_id = absint( $row['post_id'] ?? $row['id'] ?? $row['post']['id'] ?? 0 );
		$url     = nexus_normalize_seo_cockpit_url( (string) ( $row['url'] ?? $row['permalink'] ?? ( $post_id ? get_permalink( $post_id ) : '' ) ) );
		$title   = trim( (string) ( $row['title'] ?? ( $post_id ? get_the_title( $post_id ) : '' ) ) );

		if ( '' === $url && 0 === $post_id ) {
			continue;
		}

		$normalized[] = [
			'post_id'    => $post_id,
			'url'        => $url,
			'title'      => '' !== $title ? $title : $url,
			'visitors'   => nexus_get_seo_cockpit_koko_number( $row, [ 'visitors', 'unique_visitors', 'visits' ] ),
			'pageviews'  => nexus_get_seo_cockpit_koko_number( $row, [ 'pageviews', 'page_views', 'views' ] ),
			'raw'        => $row,
		];
	}

	usort(
		$normalized,
		static function ( $left, $right ) {
			return ( $right['pageviews'] <=> $left['pageviews'] );
		}
	);

	return array_slice( $normalized, 0, max( 1, absint( $limit ) ) );
}

/**
 * Return cached Koko totals for one range.
 *
 * @param string $start Start date.
 * @param string $end   End date.
 * @return array<string, float|int>
 */
function nexus_get_seo_cockpit_koko_totals( $start, $end ) {
	$cache_key = nexus_get_seo_cockpit_cache_key( 'koko_totals', [ $start, $end ] );
	$cached    = get_transient( $cache_key );

	if ( is_array( $cached ) ) {
		return $cached;
	}

	$response = nexus_seo_cockpit_koko_request(
		'totals',
		[
			'start_date' => $start,
			'end_date'   => $end,
		]
	);

	$totals = is_wp_error( $response ) ? [
		'visitors'  => 0.0,
		'pageviews' => 0.0,
		'pages'     => 0,
		'error'     => $response->get_error_message(),
	] : nexus_normalize_seo_cockpit_koko_totals( $response );

	set_transient( $cache_key, $totals, HOUR_IN_SECONDS );

	return $totals;
}

/**
 * Return cached Koko date-series data for one range.
 *
 * @param string $start Start date.
 * @param string $end   End date.
 * @return array<int, array<string, mixed>>
 */
function nexus_get_seo_cockpit_koko_series( $start, $end ) {
	$cache_key = nexus_get_seo_cockpit_cache_key( 'koko_series', [ $start, $end ] );
	$cached    = get_transient( $cache_key );

	if ( is_array( $cached ) ) {
		return $cached;
	}

	$response = nexus_seo_cockpit_koko_request(
		'stats',
		[
			'start_date' => $start,
			'end_date'   => $end,
		]
	);

	$series = is_wp_error( $response ) ? nexus_normalize_seo_cockpit_koko_date_series( $start, $end, [] ) : nexus_normalize_seo_cockpit_koko_date_series( $start, $end, $response );

	set_transient( $cache_key, $series, HOUR_IN_SECONDS );

	return $series;
}

/**
 * Return cached Koko top posts for one range.
 *
 * @param string               $start  Start date.
 * @param string               $end    End date.
 * @param int                  $limit  Max items.
 * @param array<string, mixed> $params Optional extra params.
 * @return array<int, array<string, mixed>>
 */
function nexus_get_seo_cockpit_koko_posts( $start, $end, $limit = 10, $params = [] ) {
	$cache_key = nexus_get_seo_cockpit_cache_key( 'koko_posts', [ $start, $end, $limit, wp_json_encode( $params ) ] );
	$cached    = get_transient( $cache_key );

	if ( is_array( $cached ) ) {
		return $cached;
	}

	$response = nexus_seo_cockpit_koko_request(
		'posts',
		array_merge(
			[
				'start_date' => $start,
				'end_date'   => $end,
				'limit'      => max( 1, absint( $limit ) ),
			],
			(array) $params
		)
	);

	$posts = is_wp_error( $response ) ? [] : nexus_normalize_seo_cockpit_koko_posts( $response, $limit );

	set_transient( $cache_key, $posts, HOUR_IN_SECONDS );

	return $posts;
}

/**
 * Return Koko snapshot data for the cockpit overview.
 *
 * @param array<string, string|int> $ranges Date ranges.
 * @return array<string, mixed>
 */
function nexus_get_seo_cockpit_koko_snapshot_data( $ranges ) {
	$status = nexus_get_koko_analytics_status();
	$bundle = [
		'status'     => $status,
		'available'  => ! empty( $status['rest_available'] ),
		'overview'   => [
			'current'  => [
				'visitors'  => 0.0,
				'pageviews' => 0.0,
				'pages'     => 0,
			],
			'previous' => [
				'visitors'  => 0.0,
				'pageviews' => 0.0,
				'pages'     => 0,
			],
		],
		'trend'      => [],
		'top_pages'  => [],
		'page_map'   => [],
		'error'      => '',
		'note'       => '',
	];

	if ( empty( $status['rest_available'] ) ) {
		return $bundle;
	}

	$current = nexus_get_seo_cockpit_koko_totals( (string) $ranges['current_start'], (string) $ranges['current_end'] );
	$previous = nexus_get_seo_cockpit_koko_totals( (string) $ranges['previous_start'], (string) $ranges['previous_end'] );
	$trend    = nexus_get_seo_cockpit_koko_series( (string) $ranges['current_start'], (string) $ranges['current_end'] );
	$posts    = nexus_get_seo_cockpit_koko_posts( (string) $ranges['current_start'], (string) $ranges['current_end'], 8 );

	$page_map = [];
	foreach ( $posts as $row ) {
		if ( '' !== (string) $row['url'] ) {
			$page_map[ (string) $row['url'] ] = $row;
		}
	}

	$bundle['overview']['current'] = $current;
	$bundle['overview']['previous'] = $previous;
	$bundle['trend'] = $trend;
	$bundle['top_pages'] = $posts;
	$bundle['page_map'] = $page_map;
	$bundle['error'] = isset( $current['error'] ) ? (string) $current['error'] : '';

	if (
		'' === $bundle['error']
		&& empty( $posts )
		&& (float) ( $current['visitors'] ?? 0 ) <= 0
		&& (float) ( $current['pageviews'] ?? 0 ) <= 0
	) {
		$bundle['note'] = 'Koko ist verbunden. Für den gewählten Zeitraum liefert Koko jedoch 0 Besucher und 0 Pageviews zurück.';
	}

	return $bundle;
}

/**
 * Resolve one Koko context payload for a detail URL.
 *
 * @param string               $url     Frontend URL.
 * @param array<string, mixed> $context WordPress context.
 * @param array<string, string|int> $ranges Date ranges.
 * @return array<string, mixed>
 */
function nexus_get_seo_cockpit_koko_detail_data( $url, $context, $ranges ) {
	$status = nexus_get_koko_analytics_status();
	$detail = [
		'status'    => $status,
		'available' => ! empty( $status['rest_available'] ),
		'matched'   => false,
		'current'   => [
			'visitors'  => 0.0,
			'pageviews' => 0.0,
		],
		'previous'  => [
			'visitors'  => 0.0,
			'pageviews' => 0.0,
		],
		'entry'     => null,
		'note'      => '',
	];

	if ( empty( $status['rest_available'] ) ) {
		$detail['note'] = 'Koko ist für diese Installation oder diesen Request nicht als Datenquelle verfügbar.';

		return $detail;
	}

	$post_id  = absint( $context['post_id'] ?? 0 );
	$url      = nexus_normalize_seo_cockpit_url( $url );
	$current  = nexus_get_seo_cockpit_koko_posts(
		(string) $ranges['current_start'],
		(string) $ranges['current_end'],
		200,
		$post_id ? [ 'post_id' => $post_id ] : []
	);
	$previous = nexus_get_seo_cockpit_koko_posts(
		(string) $ranges['previous_start'],
		(string) $ranges['previous_end'],
		200,
		$post_id ? [ 'post_id' => $post_id ] : []
	);

	$current_match  = null;
	$previous_match = null;

	foreach ( $current as $row ) {
		if ( ( $post_id && $post_id === (int) $row['post_id'] ) || ( '' !== $url && $url === (string) $row['url'] ) ) {
			$current_match = $row;
			break;
		}
	}

	foreach ( $previous as $row ) {
		if ( ( $post_id && $post_id === (int) $row['post_id'] ) || ( '' !== $url && $url === (string) $row['url'] ) ) {
			$previous_match = $row;
			break;
		}
	}

	if ( is_array( $current_match ) ) {
		$detail['matched'] = true;
		$detail['entry']   = $current_match;
		$detail['current'] = [
			'visitors'  => (float) ( $current_match['visitors'] ?? 0 ),
			'pageviews' => (float) ( $current_match['pageviews'] ?? 0 ),
		];
	}

	if ( is_array( $previous_match ) ) {
		$detail['previous'] = [
			'visitors'  => (float) ( $previous_match['visitors'] ?? 0 ),
			'pageviews' => (float) ( $previous_match['pageviews'] ?? 0 ),
		];
	}

	if ( ! $detail['matched'] ) {
		$detail['note'] = 'Koko ist verbunden, aber für diese URL wurde im gewählten Zeitraum kein eindeutiger Seiten-Eintrag gefunden. Das ist kein Search-Console-Fehler.';
	}

	return $detail;
}
