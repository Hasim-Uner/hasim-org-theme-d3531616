<?php
/**
 * SEO Cockpit sync, caching and historical reporting layer.
 *
 * @package Hasimuener_Journal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return the active property configured for the cockpit.
 *
 * @return string
 */
function nexus_get_seo_cockpit_property() {
	return trim( nexus_get_seo_cockpit_setting( 'property' ) );
}

/**
 * Return the refresh interval in seconds.
 *
 * @return int
 */
function nexus_get_seo_cockpit_refresh_interval_seconds( $override_hours = null ) {
	$settings = nexus_get_seo_cockpit_settings();
	$hours    = null === $override_hours ? absint( $settings['refresh_window'] ?? 12 ) : absint( $override_hours );
	$hours    = max( 1, min( 24, $hours ) );

	return $hours * HOUR_IN_SECONDS;
}

/**
 * Return the dynamic cron schedule name for the cockpit.
 *
 * @return string
 */
function nexus_get_seo_cockpit_refresh_schedule_name( $override_hours = null ) {
	return 'nexus_seo_cockpit_every_' . max( 1, min( 24, absint( nexus_get_seo_cockpit_refresh_interval_seconds( $override_hours ) / HOUR_IN_SECONDS ) ) ) . '_hours';
}

/**
 * Register the dynamic refresh interval.
 *
 * @param array<string, array<string, mixed>> $schedules Existing schedules.
 * @return array<string, array<string, mixed>>
 */
function nexus_register_seo_cockpit_cron_schedule( $schedules ) {
	$hours                     = max( 1, min( 24, absint( nexus_get_seo_cockpit_refresh_interval_seconds() / HOUR_IN_SECONDS ) ) );
	$schedules[ nexus_get_seo_cockpit_refresh_schedule_name() ] = [
		'interval' => $hours * HOUR_IN_SECONDS,
		'display'  => sprintf( 'SEO Cockpit alle %d Stunden', $hours ),
	];

	return $schedules;
}
add_filter( 'cron_schedules', 'nexus_register_seo_cockpit_cron_schedule' );

/**
 * Return the next scheduled sync timestamp.
 *
 * @return int
 */
function nexus_get_seo_cockpit_next_sync_at() {
	return (int) wp_next_scheduled( 'nexus_seo_cockpit_refresh_snapshot' );
}

/**
 * Ensure the automatic SEO cockpit refresh event is scheduled correctly.
 *
 * @param bool $force Force rescheduling even if the schedule matches.
 * @return void
 */
function nexus_maybe_reschedule_seo_cockpit_refresh_event( $force = false, $override_hours = null ) {
	if ( wp_installing() ) {
		return;
	}

	$hook            = 'nexus_seo_cockpit_refresh_snapshot';
	$desired_schedule = nexus_get_seo_cockpit_refresh_schedule_name( $override_hours );
	$current_event   = function_exists( 'wp_get_scheduled_event' ) ? wp_get_scheduled_event( $hook ) : null;

	if ( ! $force && $current_event && isset( $current_event->schedule ) && $desired_schedule === $current_event->schedule ) {
		return;
	}

	if ( $current_event ) {
		wp_clear_scheduled_hook( $hook );
	}

	wp_schedule_event( time() + ( 5 * MINUTE_IN_SECONDS ), $desired_schedule, $hook );
}
add_action( 'init', 'nexus_maybe_reschedule_seo_cockpit_refresh_event', 20 );

/**
 * Return the lock transient key for sync operations.
 *
 * @return string
 */
function nexus_get_seo_cockpit_sync_lock_key() {
	return 'nexus_seo_cockpit_sync_lock';
}

/**
 * Acquire the sync lock if currently free.
 *
 * @param string $source Sync source label.
 * @return bool
 */
function nexus_acquire_seo_cockpit_sync_lock( $source ) {
	$key    = nexus_get_seo_cockpit_sync_lock_key();
	$locked = get_transient( $key );

	if ( is_array( $locked ) && ! empty( $locked['expires_at'] ) && (int) $locked['expires_at'] > time() ) {
		return false;
	}

	set_transient(
		$key,
		[
			'source'     => sanitize_key( (string) $source ),
			'started_at' => time(),
			'expires_at' => time() + ( 10 * MINUTE_IN_SECONDS ),
		],
		10 * MINUTE_IN_SECONDS
	);

	return true;
}

/**
 * Release the sync lock.
 *
 * @return void
 */
function nexus_release_seo_cockpit_sync_lock() {
	delete_transient( nexus_get_seo_cockpit_sync_lock_key() );
}

/**
 * Return a date-range payload for the selected reporting window.
 *
 * @param int $window_days Number of days.
 * @return array<string, string|int>
 */
function nexus_get_seo_cockpit_date_ranges( $window_days = 28 ) {
	$allowed     = nexus_get_seo_cockpit_allowed_ranges();
	$window_days = in_array( absint( $window_days ), $allowed, true ) ? absint( $window_days ) : 28;
	$lag_days    = 3;
	$end_ts      = current_time( 'timestamp' ) - ( $lag_days * DAY_IN_SECONDS );

	return [
		'window_days'    => $window_days,
		'current_start'  => wp_date( 'Y-m-d', $end_ts - ( ( $window_days - 1 ) * DAY_IN_SECONDS ) ),
		'current_end'    => wp_date( 'Y-m-d', $end_ts ),
		'previous_start' => wp_date( 'Y-m-d', $end_ts - ( ( ( $window_days * 2 ) - 1 ) * DAY_IN_SECONDS ) ),
		'previous_end'   => wp_date( 'Y-m-d', $end_ts - ( $window_days * DAY_IN_SECONDS ) ),
	];
}

/**
 * Return aggregated metrics for one date range.
 *
 * @param string               $property Site property.
 * @param string               $start    Start date.
 * @param string               $end      End date.
 * @param array<int, array<string, string>> $filters Optional filters.
 * @return array<string, float>|WP_Error
 */
function nexus_get_seo_cockpit_aggregate_metrics( $property, $start, $end, $filters = [] ) {
	$rows = nexus_get_seo_cockpit_report_rows( $property, $start, $end, [], $filters, 1 );

	if ( is_wp_error( $rows ) ) {
		return $rows;
	}

	$row = ! empty( $rows[0] ) && is_array( $rows[0] ) ? $rows[0] : [];

	return [
		'clicks'      => (float) ( $row['clicks'] ?? 0 ),
		'impressions' => (float) ( $row['impressions'] ?? 0 ),
		'ctr'         => (float) ( $row['ctr'] ?? 0 ),
		'position'    => (float) ( $row['position'] ?? 0 ),
	];
}

/**
 * Query one Search Console overview block.
 *
 * @param string $property Site property.
 * @param string $start    Start date.
 * @param string $end      End date.
 * @return array<string, float>|WP_Error
 */
function nexus_get_seo_cockpit_overview_metrics( $property, $start, $end ) {
	return nexus_get_seo_cockpit_aggregate_metrics( $property, $start, $end );
}

/**
 * Query one Search Console dimension report.
 *
 * @param string $property  Site property.
 * @param string $start     Start date.
 * @param string $end       End date.
 * @param string $dimension Report dimension.
 * @param int    $limit     Max row count.
 * @return array<int, array<string, mixed>>|WP_Error
 */
function nexus_get_seo_cockpit_dimension_rows( $property, $start, $end, $dimension, $limit = 10 ) {
	return nexus_get_seo_cockpit_report_rows( $property, $start, $end, [ $dimension ], [], $limit );
}

/**
 * Return one date-based Search Console trend series.
 *
 * @param string               $property Site property.
 * @param string               $start    Start date.
 * @param string               $end      End date.
 * @param array<int, array<string, string>> $filters Optional filters.
 * @return array<int, array<string, mixed>>|WP_Error
 */
function nexus_get_seo_cockpit_date_series( $property, $start, $end, $filters = [] ) {
	$rows = nexus_get_seo_cockpit_report_rows( $property, $start, $end, [ 'date' ], $filters, 100 );

	if ( is_wp_error( $rows ) ) {
		return $rows;
	}

	return nexus_normalize_seo_cockpit_date_series( $start, $end, $rows );
}

/**
 * Normalize date-series rows so missing days still render correctly.
 *
 * @param string                            $start Start date.
 * @param string                            $end   End date.
 * @param array<int, array<string, mixed>> $rows  Raw Search Console rows.
 * @return array<int, array<string, mixed>>
 */
function nexus_normalize_seo_cockpit_date_series( $start, $end, $rows ) {
	$indexed = [];

	foreach ( (array) $rows as $row ) {
		$date = isset( $row['keys'][0] ) ? (string) $row['keys'][0] : '';
		if ( '' === $date ) {
			continue;
		}

		$indexed[ $date ] = [
			'date'        => $date,
			'clicks'      => (float) ( $row['clicks'] ?? 0 ),
			'impressions' => (float) ( $row['impressions'] ?? 0 ),
			'ctr'         => (float) ( $row['ctr'] ?? 0 ),
			'position'    => (float) ( $row['position'] ?? 0 ),
			'has_data'    => true,
		];
	}

	$series = [];
	$cursor = strtotime( $start . ' 00:00:00' );
	$end_ts = strtotime( $end . ' 00:00:00' );

	while ( false !== $cursor && false !== $end_ts && $cursor <= $end_ts ) {
		$date = gmdate( 'Y-m-d', $cursor );

		$series[] = $indexed[ $date ] ?? [
			'date'        => $date,
			'clicks'      => 0.0,
			'impressions' => 0.0,
			'ctr'         => 0.0,
			'position'    => 0.0,
			'has_data'    => false,
		];

		$cursor += DAY_IN_SECONDS;
	}

	return $series;
}

/**
 * Return the transient cache key for the SEO snapshot.
 *
 * @param int|null $range_days Optional range.
 * @return string
 */
function nexus_get_seo_cockpit_snapshot_cache_key( $range_days = null ) {
	$range_days = null === $range_days ? nexus_get_seo_cockpit_requested_range_days() : absint( $range_days );

	return nexus_get_seo_cockpit_cache_key(
		'snapshot',
		[
			nexus_get_seo_cockpit_property(),
			$range_days,
		]
	);
}

/**
 * Delete the cached cockpit snapshot and dependent caches.
 *
 * @return void
 */
function nexus_delete_seo_cockpit_snapshot_cache() {
	nexus_bump_seo_cockpit_cache_version();

	$runtime = nexus_get_seo_cockpit_runtime();
	if ( is_array( $runtime ) ) {
		unset( $runtime['cache_expires_at'] );
		$runtime['next_sync_at'] = nexus_get_seo_cockpit_next_sync_at();
		nexus_update_seo_cockpit_runtime( $runtime );
	}
}

/**
 * Persist the last sync result for dashboard and diagnostics.
 *
 * @param string                      $source Sync source.
 * @param array<string, mixed>|WP_Error $result Sync result.
 * @return void
 */
function nexus_record_seo_cockpit_sync_result( $source, $result ) {
	$runtime = nexus_get_seo_cockpit_runtime();
	$runtime = is_array( $runtime ) ? $runtime : [];
	$source  = sanitize_key( (string) $source );

	$runtime['last_sync_source'] = $source ?: 'manual';
	$runtime['last_sync_at']     = current_time( 'timestamp' );
	$runtime['next_sync_at']     = nexus_get_seo_cockpit_next_sync_at();

	if ( is_wp_error( $result ) ) {
		$runtime['last_sync_status']  = 'error';
		$runtime['last_sync_message'] = $result->get_error_message();
		$runtime['last_error_code']   = (string) $result->get_error_code();
		$runtime['last_error_message'] = $result->get_error_message();
	} else {
		$runtime['last_sync_status']   = 'success';
		$runtime['last_sync_message']  = 'Snapshot erfolgreich aktualisiert.';
		$runtime['last_error_code']    = '';
		$runtime['last_error_message'] = '';
		$runtime['cache_expires_at']   = isset( $result['cache_expires_at'] ) ? (int) $result['cache_expires_at'] : 0;
	}

	nexus_update_seo_cockpit_runtime( $runtime );
}

/**
 * Return runtime information combined with the current schedule state.
 *
 * @return array<string, mixed>
 */
function nexus_get_seo_cockpit_runtime_summary() {
	$runtime                  = nexus_get_seo_cockpit_runtime();
	$runtime['next_sync_at']  = nexus_get_seo_cockpit_next_sync_at();
	$runtime['refresh_hours'] = absint( nexus_get_seo_cockpit_refresh_interval_seconds() / HOUR_IN_SECONDS );

	return $runtime;
}

/**
 * Refresh the cached SEO cockpit snapshot and persist its runtime status.
 *
 * @param string   $source     Sync source label.
 * @param int|null $range_days Optional selected range.
 * @return array<string, mixed>|WP_Error
 */
function nexus_run_seo_cockpit_sync( $source = 'manual', $range_days = null ) {
	if ( ! nexus_acquire_seo_cockpit_sync_lock( $source ) ) {
		return new WP_Error( 'nexus_seo_sync_locked', 'Es läuft bereits eine Synchronisierung. Bitte gleich erneut versuchen.' );
	}

	try {
		nexus_delete_seo_cockpit_snapshot_cache();
		$result = nexus_get_seo_cockpit_snapshot( true, $range_days );
		nexus_record_seo_cockpit_sync_result( $source, $result );
	} finally {
		nexus_release_seo_cockpit_sync_lock();
	}

	return $result;
}

/**
 * Force-refresh the Search Console cache.
 *
 * @return void
 */
function nexus_handle_seo_cockpit_refresh_action() {
	if ( ! nexus_current_user_can_manage_seo_cockpit() ) {
		wp_die( 'Nicht erlaubt.' );
	}

	check_admin_referer( 'nexus_seo_cockpit_refresh' );

	$range = isset( $_POST['range'] ) ? absint( wp_unslash( $_POST['range'] ) ) : 28;
	$url   = isset( $_POST['detail_url'] ) ? sanitize_text_field( (string) wp_unslash( $_POST['detail_url'] ) ) : '';
	$url   = nexus_normalize_seo_cockpit_url( $url );

	$result = nexus_run_seo_cockpit_sync( 'manual', $range );
	$target = '' !== $url ? nexus_get_seo_cockpit_detail_url( $url, [ 'range' => $range ] ) : nexus_get_seo_cockpit_dashboard_url( [ 'range' => $range ] );

	wp_safe_redirect(
		add_query_arg(
			[
				'nexus_seo_notice' => is_wp_error( $result ) ? ( 'nexus_seo_sync_locked' === $result->get_error_code() ? 'refresh_locked' : 'refresh_failed' ) : 'refresh_success',
			],
			$target
		)
	);
	exit;
}
add_action( 'admin_post_nexus_seo_cockpit_refresh', 'nexus_handle_seo_cockpit_refresh_action' );

/**
 * Refresh the SEO cockpit snapshot from WP-Cron.
 *
 * @return void
 */
function nexus_handle_seo_cockpit_cron_refresh() {
	$config = nexus_get_seo_cockpit_search_console_config();
	$tokens = nexus_get_seo_cockpit_tokens();

	if ( '' === $config['property'] || '' === $config['client_id'] || '' === $config['client_secret'] || '' === (string) ( $tokens['access_token'] ?? '' ) ) {
		return;
	}

	nexus_run_seo_cockpit_sync( 'cron', 28 );
}
add_action( 'nexus_seo_cockpit_refresh_snapshot', 'nexus_handle_seo_cockpit_cron_refresh' );

/**
 * Build or load the cached SEO cockpit snapshot.
 *
 * @param bool     $force      Force refresh.
 * @param int|null $range_days Optional selected range.
 * @return array<string, mixed>|WP_Error
 */
function nexus_get_seo_cockpit_snapshot( $force = false, $range_days = null ) {
	$property   = nexus_get_seo_cockpit_property();
	$range_days = null === $range_days ? nexus_get_seo_cockpit_requested_range_days() : absint( $range_days );
	$ranges     = nexus_get_seo_cockpit_date_ranges( $range_days );

	if ( '' === $property ) {
		return new WP_Error( 'nexus_seo_missing_property', 'Es ist noch keine Search-Console-Property hinterlegt.' );
	}

	$cache_key = nexus_get_seo_cockpit_snapshot_cache_key( $range_days );
	$cached    = get_transient( $cache_key );
	if ( ! $force && is_array( $cached ) ) {
		return $cached;
	}

	$top_pages_cap  = nexus_get_seo_cockpit_row_cap( 'top_pages' );
	$top_queries_cap = nexus_get_seo_cockpit_row_cap( 'top_queries' );
	$top_devices_cap = nexus_get_seo_cockpit_row_cap( 'top_devices' );
	$page_rows_cap   = nexus_get_seo_cockpit_row_cap( 'page_rows' );
	$query_rows_cap  = nexus_get_seo_cockpit_row_cap( 'query_page_rows' );

	$current_overview = nexus_get_seo_cockpit_overview_metrics( $property, $ranges['current_start'], $ranges['current_end'] );
	if ( is_wp_error( $current_overview ) ) {
		return $current_overview;
	}

	$previous_overview = nexus_get_seo_cockpit_overview_metrics( $property, $ranges['previous_start'], $ranges['previous_end'] );
	if ( is_wp_error( $previous_overview ) ) {
		return $previous_overview;
	}

	$trend = nexus_get_seo_cockpit_date_series( $property, $ranges['current_start'], $ranges['current_end'] );
	if ( is_wp_error( $trend ) ) {
		return $trend;
	}

	$top_pages = nexus_get_seo_cockpit_report_rows(
		$property,
		$ranges['current_start'],
		$ranges['current_end'],
		[ 'page' ],
		[],
		(int) $top_pages_cap['limit'],
		$top_pages_cap
	);
	if ( is_wp_error( $top_pages ) ) {
		return $top_pages;
	}

	$top_queries = nexus_get_seo_cockpit_report_rows(
		$property,
		$ranges['current_start'],
		$ranges['current_end'],
		[ 'query' ],
		[],
		(int) $top_queries_cap['limit'],
		$top_queries_cap
	);
	if ( is_wp_error( $top_queries ) ) {
		return $top_queries;
	}

	$top_devices = nexus_get_seo_cockpit_report_rows(
		$property,
		$ranges['current_start'],
		$ranges['current_end'],
		[ 'device' ],
		[],
		(int) $top_devices_cap['limit'],
		$top_devices_cap
	);
	if ( is_wp_error( $top_devices ) ) {
		return $top_devices;
	}

	$current_page_rows = nexus_get_seo_cockpit_report_rows(
		$property,
		$ranges['current_start'],
		$ranges['current_end'],
		[ 'page' ],
		[],
		(int) $page_rows_cap['limit'],
		$page_rows_cap
	);
	if ( is_wp_error( $current_page_rows ) ) {
		return $current_page_rows;
	}

	$previous_page_rows = nexus_get_seo_cockpit_report_rows(
		$property,
		$ranges['previous_start'],
		$ranges['previous_end'],
		[ 'page' ],
		[],
		(int) $page_rows_cap['limit'],
		$page_rows_cap
	);
	if ( is_wp_error( $previous_page_rows ) ) {
		return $previous_page_rows;
	}

	$query_page_rows = nexus_get_seo_cockpit_report_rows(
		$property,
		$ranges['current_start'],
		$ranges['current_end'],
		[ 'page', 'query' ],
		[],
		(int) $query_rows_cap['limit'],
		$query_rows_cap
	);
	if ( is_wp_error( $query_page_rows ) ) {
		return $query_page_rows;
	}

	$previous_query_page_rows = nexus_get_seo_cockpit_report_rows(
		$property,
		$ranges['previous_start'],
		$ranges['previous_end'],
		[ 'page', 'query' ],
		[],
		(int) $query_rows_cap['limit'],
		$query_rows_cap
	);
	if ( is_wp_error( $previous_query_page_rows ) ) {
		return $previous_query_page_rows;
	}

	$sitemaps = nexus_get_seo_cockpit_sitemaps( $force );
	if ( is_wp_error( $sitemaps ) ) {
		$sitemaps = [];
	}

	$page_contexts = function_exists( 'nexus_get_seo_cockpit_page_context_map' ) ? nexus_get_seo_cockpit_page_context_map( $current_page_rows ) : [];
	$koko         = function_exists( 'nexus_get_seo_cockpit_koko_snapshot_data' ) ? nexus_get_seo_cockpit_koko_snapshot_data( $ranges ) : [];
	$leads        = function_exists( 'nexus_get_seo_cockpit_lead_snapshot_data' ) ? nexus_get_seo_cockpit_lead_snapshot_data( $ranges ) : [];

	$snapshot = [
		'generated_at'              => current_time( 'timestamp' ),
		'cache_expires_at'          => current_time( 'timestamp' ) + nexus_get_seo_cockpit_refresh_interval_seconds(),
		'property'                  => $property,
		'range_days'                => $range_days,
		'ranges'                    => $ranges,
		'overview'                  => [
			'current'  => $current_overview,
			'previous' => $previous_overview,
		],
		'trend'                     => $trend,
		'top_pages'                 => $top_pages,
		'top_queries'               => $top_queries,
		'top_devices'               => $top_devices,
		'current_page_rows'         => $current_page_rows,
		'previous_page_rows'        => $previous_page_rows,
		'query_page_rows'           => $query_page_rows,
		'previous_query_page_rows'  => $previous_query_page_rows,
		'page_contexts'             => $page_contexts,
		'sitemaps'                  => $sitemaps,
		'koko'                      => $koko,
		'leads'                     => $leads,
	];

	if ( function_exists( 'nexus_get_seo_cockpit_insights' ) ) {
		$snapshot['insights'] = nexus_get_seo_cockpit_insights( $snapshot );
	}

	if ( function_exists( 'nexus_get_seo_cockpit_problem_pages' ) ) {
		$snapshot['problem_pages'] = nexus_get_seo_cockpit_problem_pages( $snapshot );
	}

	set_transient( $cache_key, $snapshot, nexus_get_seo_cockpit_refresh_interval_seconds() );
	nexus_record_seo_cockpit_sync_result( 'cache_build', $snapshot );

	return $snapshot;
}
