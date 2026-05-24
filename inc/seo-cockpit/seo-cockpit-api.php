<?php
/**
 * SEO Cockpit OAuth, token handling and Google API access.
 *
 * @package Hasimuener_Journal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Build a state token for the Google OAuth handshake.
 *
 * @return string
 */
function nexus_create_seo_cockpit_oauth_state() {
	$state = wp_generate_password( 32, false, false );

	set_transient(
		'nexus_seo_cockpit_oauth_' . $state,
		[
			'user_id' => get_current_user_id(),
			'created' => time(),
		],
		15 * MINUTE_IN_SECONDS
	);

	return $state;
}

/**
 * Return the persisted state payload for an OAuth state token.
 *
 * @param string $state OAuth state token.
 * @return array<string, mixed>
 */
function nexus_get_seo_cockpit_oauth_state( $state ) {
	$payload = get_transient( 'nexus_seo_cockpit_oauth_' . $state );

	return is_array( $payload ) ? $payload : [];
}

/**
 * Delete a persisted OAuth state token.
 *
 * @param string $state OAuth state token.
 * @return void
 */
function nexus_delete_seo_cockpit_oauth_state( $state ) {
	delete_transient( 'nexus_seo_cockpit_oauth_' . $state );
}

/**
 * Redirect to the Google consent page.
 *
 * @return void
 */
function nexus_handle_seo_cockpit_connect_action() {
	if ( ! nexus_current_user_can_manage_seo_cockpit() ) {
		wp_die( 'Nicht erlaubt.' );
	}

	check_admin_referer( 'nexus_seo_cockpit_connect' );

	if ( ! nexus_has_seo_cockpit_search_console_credentials() ) {
		wp_safe_redirect( nexus_get_seo_cockpit_dashboard_url( [ 'nexus_seo_notice' => 'missing_credentials' ] ) );
		exit;
	}

	$config = nexus_get_seo_cockpit_search_console_config();
	$state  = nexus_create_seo_cockpit_oauth_state();
	$params = [
		'client_id'              => $config['client_id'],
		'redirect_uri'           => $config['redirect_uri'],
		'response_type'          => 'code',
		'scope'                  => $config['scope'],
		'access_type'            => 'offline',
		'include_granted_scopes' => 'true',
		'prompt'                 => 'consent',
		'state'                  => $state,
	];

	wp_redirect( 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query( $params, '', '&', PHP_QUERY_RFC3986 ) );
	exit;
}
add_action( 'admin_post_nexus_seo_cockpit_connect', 'nexus_handle_seo_cockpit_connect_action' );

/**
 * Handle the Google OAuth callback for the cockpit.
 *
 * @return void
 */
function nexus_handle_seo_cockpit_oauth_callback() {
	if ( ! nexus_current_user_can_manage_seo_cockpit() ) {
		wp_die( 'Nicht erlaubt.' );
	}

	$state   = isset( $_GET['state'] ) ? sanitize_text_field( (string) wp_unslash( $_GET['state'] ) ) : '';
	$code    = isset( $_GET['code'] ) ? sanitize_text_field( (string) wp_unslash( $_GET['code'] ) ) : '';
	$error   = isset( $_GET['error'] ) ? sanitize_text_field( (string) wp_unslash( $_GET['error'] ) ) : '';
	$payload = '' !== $state ? nexus_get_seo_cockpit_oauth_state( $state ) : [];

	if ( '' === $state || empty( $payload ) || (int) ( $payload['user_id'] ?? 0 ) !== get_current_user_id() ) {
		wp_safe_redirect( nexus_get_seo_cockpit_dashboard_url( [ 'nexus_seo_notice' => 'oauth_state_invalid' ] ) );
		exit;
	}

	nexus_delete_seo_cockpit_oauth_state( $state );

	if ( '' !== $error ) {
		wp_safe_redirect( nexus_get_seo_cockpit_dashboard_url( [ 'nexus_seo_notice' => 'oauth_denied' ] ) );
		exit;
	}

	if ( '' === $code ) {
		wp_safe_redirect( nexus_get_seo_cockpit_dashboard_url( [ 'nexus_seo_notice' => 'oauth_missing_code' ] ) );
		exit;
	}

	$config   = nexus_get_seo_cockpit_search_console_config();
	$response = wp_remote_post(
		'https://oauth2.googleapis.com/token',
		[
			'timeout' => 20,
			'body'    => [
				'code'          => $code,
				'client_id'     => $config['client_id'],
				'client_secret' => $config['client_secret'],
				'redirect_uri'  => $config['redirect_uri'],
				'grant_type'    => 'authorization_code',
			],
		]
	);

	if ( is_wp_error( $response ) ) {
		wp_safe_redirect( nexus_get_seo_cockpit_dashboard_url( [ 'nexus_seo_notice' => 'oauth_exchange_failed' ] ) );
		exit;
	}

	$status  = (int) wp_remote_retrieve_response_code( $response );
	$decoded = json_decode( wp_remote_retrieve_body( $response ), true );
	$decoded = is_array( $decoded ) ? $decoded : [];

	if ( $status < 200 || $status >= 300 || empty( $decoded['access_token'] ) ) {
		wp_safe_redirect( nexus_get_seo_cockpit_dashboard_url( [ 'nexus_seo_notice' => 'oauth_exchange_failed' ] ) );
		exit;
	}

	$current_tokens = nexus_get_seo_cockpit_tokens();
	$tokens         = [
		'access_token'  => sanitize_text_field( (string) $decoded['access_token'] ),
		'refresh_token' => sanitize_text_field( (string) ( $decoded['refresh_token'] ?? ( $current_tokens['refresh_token'] ?? '' ) ) ),
		'expires_at'    => current_time( 'timestamp' ) + max( 60, absint( $decoded['expires_in'] ?? 3600 ) ),
		'token_type'    => sanitize_text_field( (string) ( $decoded['token_type'] ?? 'Bearer' ) ),
		'scope'         => sanitize_text_field( (string) ( $decoded['scope'] ?? nexus_get_seo_cockpit_scope() ) ),
		'updated_at'    => current_time( 'timestamp' ),
	];

	nexus_update_seo_cockpit_tokens( $tokens );
	if ( function_exists( 'nexus_delete_seo_cockpit_snapshot_cache' ) ) {
		nexus_delete_seo_cockpit_snapshot_cache();
	}

	wp_safe_redirect( nexus_get_seo_cockpit_dashboard_url( [ 'nexus_seo_notice' => 'oauth_connected' ] ) );
	exit;
}
add_action( 'admin_post_nexus_seo_cockpit_oauth_callback', 'nexus_handle_seo_cockpit_oauth_callback' );

/**
 * Delete the current Search Console token payload.
 *
 * @return void
 */
function nexus_handle_seo_cockpit_disconnect_action() {
	if ( ! nexus_current_user_can_manage_seo_cockpit() ) {
		wp_die( 'Nicht erlaubt.' );
	}

	check_admin_referer( 'nexus_seo_cockpit_disconnect' );

	delete_option( nexus_get_seo_cockpit_token_option_name() );
	delete_option( nexus_get_seo_cockpit_runtime_option_name() );
	if ( function_exists( 'nexus_delete_seo_cockpit_snapshot_cache' ) ) {
		nexus_delete_seo_cockpit_snapshot_cache();
	}

	wp_safe_redirect( nexus_get_seo_cockpit_dashboard_url( [ 'nexus_seo_notice' => 'oauth_disconnected' ] ) );
	exit;
}
add_action( 'admin_post_nexus_seo_cockpit_disconnect', 'nexus_handle_seo_cockpit_disconnect_action' );

/**
 * Refresh the Search Console access token when needed.
 *
 * @return array<string, mixed>|WP_Error
 */
function nexus_refresh_seo_cockpit_access_token() {
	$config = nexus_get_seo_cockpit_search_console_config();
	$tokens = nexus_get_seo_cockpit_tokens();

	if ( '' === (string) ( $tokens['refresh_token'] ?? '' ) ) {
		return new WP_Error( 'nexus_seo_missing_refresh_token', 'Es ist kein Refresh-Token für Search Console gespeichert.' );
	}

	$response = wp_remote_post(
		'https://oauth2.googleapis.com/token',
		[
			'timeout' => 20,
			'body'    => [
				'client_id'     => $config['client_id'],
				'client_secret' => $config['client_secret'],
				'refresh_token' => $tokens['refresh_token'],
				'grant_type'    => 'refresh_token',
			],
		]
	);

	if ( is_wp_error( $response ) ) {
		return $response;
	}

	$status  = (int) wp_remote_retrieve_response_code( $response );
	$decoded = json_decode( wp_remote_retrieve_body( $response ), true );
	$decoded = is_array( $decoded ) ? $decoded : [];

	if ( $status < 200 || $status >= 300 || empty( $decoded['access_token'] ) ) {
		return new WP_Error( 'nexus_seo_refresh_failed', 'Das Refresh-Token konnte nicht erneuert werden.' );
	}

	$tokens['access_token'] = sanitize_text_field( (string) $decoded['access_token'] );
	$tokens['expires_at']   = current_time( 'timestamp' ) + max( 60, absint( $decoded['expires_in'] ?? 3600 ) );
	$tokens['updated_at']   = current_time( 'timestamp' );

	nexus_update_seo_cockpit_tokens( $tokens );

	return $tokens;
}

/**
 * Return a valid Search Console access token.
 *
 * @return string|WP_Error
 */
function nexus_get_seo_cockpit_access_token() {
	$tokens = nexus_get_seo_cockpit_tokens();

	if ( '' === (string) ( $tokens['access_token'] ?? '' ) ) {
		return new WP_Error( 'nexus_seo_missing_access_token', 'Die Search Console ist noch nicht verbunden.' );
	}

	$expires_at = absint( $tokens['expires_at'] ?? 0 );
	if ( $expires_at > current_time( 'timestamp' ) + 90 ) {
		return (string) $tokens['access_token'];
	}

	$refreshed = nexus_refresh_seo_cockpit_access_token();
	if ( is_wp_error( $refreshed ) ) {
		return $refreshed;
	}

	return (string) $refreshed['access_token'];
}

/**
 * Perform one authenticated Google API request.
 *
 * @param string      $method      HTTP method.
 * @param string      $url         Absolute Google API URL.
 * @param array       $body        Optional JSON body.
 * @param bool        $allow_retry Whether one retry after token refresh is allowed.
 * @return array<string, mixed>|WP_Error
 */
function nexus_seo_cockpit_google_request( $method, $url, $body = [], $allow_retry = true ) {
	$access_token = nexus_get_seo_cockpit_access_token();
	if ( is_wp_error( $access_token ) ) {
		return $access_token;
	}

	$args = [
		'method'  => strtoupper( (string) $method ),
		'timeout' => 25,
		'headers' => [
			'Authorization' => 'Bearer ' . $access_token,
			'Accept'        => 'application/json',
		],
	];

	if ( ! empty( $body ) ) {
		$args['headers']['Content-Type'] = 'application/json; charset=utf-8';
		$args['body']                    = wp_json_encode( $body );
	}

	$response = wp_remote_request( esc_url_raw( (string) $url ), $args );
	if ( is_wp_error( $response ) ) {
		return $response;
	}

	$status  = (int) wp_remote_retrieve_response_code( $response );
	$decoded = json_decode( wp_remote_retrieve_body( $response ), true );
	$decoded = is_array( $decoded ) ? $decoded : [];

	if ( 401 === $status && $allow_retry ) {
		$refreshed = nexus_refresh_seo_cockpit_access_token();
		if ( ! is_wp_error( $refreshed ) ) {
			return nexus_seo_cockpit_google_request( $method, $url, $body, false );
		}
	}

	if ( $status < 200 || $status >= 300 ) {
		$message = isset( $decoded['error']['message'] ) ? (string) $decoded['error']['message'] : 'Google API Anfrage fehlgeschlagen.';
		return new WP_Error(
			'nexus_seo_api_error',
			$message,
			[
				'status'   => $status,
				'response' => $decoded,
				'url'      => $url,
			]
		);
	}

	return $decoded;
}

/**
 * Perform one Search Console API request.
 *
 * @param string $method      HTTP method.
 * @param string $path        API path.
 * @param array  $body        Optional JSON body.
 * @param bool   $allow_retry Whether a single token-refresh retry is allowed.
 * @return array<string, mixed>|WP_Error
 */
function nexus_seo_cockpit_search_console_request( $method, $path, $body = [], $allow_retry = true ) {
	return nexus_seo_cockpit_google_request(
		$method,
		nexus_get_seo_cockpit_api_base_url() . $path,
		$body,
		$allow_retry
	);
}

/**
 * Query a Search Console report with optional dimensions and filters.
 *
 * @param string               $property    Site property.
 * @param string               $start       Start date.
 * @param string               $end         End date.
 * @param array<int, string>   $dimensions  Dimensions.
 * @param array<int, array<string, string>> $filters Dimension filters.
 * @param int                  $limit       Maximum row count.
 * @param array<string, mixed> $options     Optional paging options.
 * @return array<int, array<string, mixed>>|WP_Error
 */
function nexus_get_seo_cockpit_report_rows( $property, $start, $end, $dimensions = [], $filters = [], $limit = 10, $options = [] ) {
	$options       = wp_parse_args(
		(array) $options,
		[
			'paginate'  => false,
			'page_size' => max( 1, absint( $limit ) ),
			'max_pages' => 1,
			'start_row' => 0,
		]
	);
	$desired_limit = max( 1, absint( $limit ) );
	$page_size     = max( 1, absint( $options['page_size'] ) );
	$max_pages     = max( 1, absint( $options['max_pages'] ) );
	$start_row     = max( 0, absint( $options['start_row'] ) );
	$paginate      = ! empty( $options['paginate'] );
	$rows          = [];
	$pages_loaded  = 0;
	$body          = [
		'startDate'  => (string) $start,
		'endDate'    => (string) $end,
		'searchType' => 'web',
	];

	if ( ! empty( $dimensions ) ) {
		$body['dimensions'] = array_values(
			array_filter(
				array_map(
					static function ( $dimension ) {
						return sanitize_key( (string) $dimension );
					},
					(array) $dimensions
				)
			)
		);
	}

	if ( ! empty( $filters ) ) {
		$dimension_filters = [];

		foreach ( (array) $filters as $filter ) {
			$dimension  = sanitize_key( (string) ( $filter['dimension'] ?? '' ) );
			$expression = trim( (string) ( $filter['expression'] ?? '' ) );

			if ( '' === $dimension || '' === $expression ) {
				continue;
			}

			$dimension_filters[] = [
				'dimension'  => $dimension,
				'operator'   => 'equals',
				'expression' => $expression,
			];
		}

		if ( ! empty( $dimension_filters ) ) {
			$body['dimensionFilterGroups'] = [
				[
					'groupType' => 'and',
					'filters'   => $dimension_filters,
				],
			];
		}
	}

	do {
		$remaining        = max( 1, $desired_limit - count( $rows ) );
		$body['rowLimit'] = min( $page_size, $remaining );
		$body['startRow'] = $start_row;

		$response = nexus_seo_cockpit_search_console_request(
			'POST',
			'/sites/' . rawurlencode( $property ) . '/searchAnalytics/query',
			$body
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$batch = isset( $response['rows'] ) && is_array( $response['rows'] ) ? array_values( $response['rows'] ) : [];
		$rows  = array_merge( $rows, $batch );

		$pages_loaded++;
		$start_row += count( $batch );

		if ( ! $paginate || count( $rows ) >= $desired_limit || count( $batch ) < (int) $body['rowLimit'] || $pages_loaded >= $max_pages ) {
			break;
		}
	} while ( true );

	return array_slice( $rows, 0, $desired_limit );
}

/**
 * Return cached Search Console sites.
 *
 * @param bool $force Force refresh.
 * @return array<int, array<string, mixed>>|WP_Error
 */
function nexus_get_seo_cockpit_sites( $force = false ) {
	$cache_key = nexus_get_seo_cockpit_cache_key( 'sites', [ nexus_get_seo_cockpit_property() ] );
	$cached    = get_transient( $cache_key );

	if ( ! $force && is_array( $cached ) ) {
		return $cached;
	}

	$response = nexus_seo_cockpit_search_console_request( 'GET', '/sites' );
	if ( is_wp_error( $response ) ) {
		return $response;
	}

	$sites = isset( $response['siteEntry'] ) && is_array( $response['siteEntry'] ) ? array_values( $response['siteEntry'] ) : [];
	set_transient( $cache_key, $sites, HOUR_IN_SECONDS );

	return $sites;
}

/**
 * Return cached sitemap data for the active property.
 *
 * @param bool $force Force refresh.
 * @return array<int, array<string, mixed>>|WP_Error
 */
function nexus_get_seo_cockpit_sitemaps( $force = false ) {
	$property = nexus_get_seo_cockpit_property();

	if ( '' === $property ) {
		return new WP_Error( 'nexus_seo_missing_property', 'Es ist noch keine Search-Console-Property hinterlegt.' );
	}

	$cache_key = nexus_get_seo_cockpit_cache_key( 'sitemaps', [ $property ] );
	$cached    = get_transient( $cache_key );

	if ( ! $force && is_array( $cached ) ) {
		return $cached;
	}

	$response = nexus_seo_cockpit_search_console_request( 'GET', '/sites/' . rawurlencode( $property ) . '/sitemaps' );
	if ( is_wp_error( $response ) ) {
		return $response;
	}

	$sitemaps = isset( $response['sitemap'] ) && is_array( $response['sitemap'] ) ? array_values( $response['sitemap'] ) : [];
	set_transient( $cache_key, $sitemaps, HOUR_IN_SECONDS );

	return $sitemaps;
}

/**
 * Return the cached URL inspection result if present.
 *
 * @param string $url Frontend URL.
 * @return array<string, mixed>|null
 */
function nexus_get_seo_cockpit_cached_url_inspection( $url ) {
	$url = nexus_normalize_seo_cockpit_url( $url );

	if ( '' === $url ) {
		return null;
	}

	$cache_key = nexus_get_seo_cockpit_cache_key( 'inspection', [ nexus_get_seo_cockpit_property(), $url ] );
	$cached    = get_transient( $cache_key );

	return is_array( $cached ) ? $cached : null;
}

/**
 * Request and cache one URL Inspection result.
 *
 * @param string $url   Frontend URL.
 * @param bool   $force Force refresh.
 * @return array<string, mixed>|WP_Error
 */
function nexus_get_seo_cockpit_url_inspection( $url, $force = false ) {
	$property = nexus_get_seo_cockpit_property();
	$url      = nexus_normalize_seo_cockpit_url( $url );

	if ( '' === $property ) {
		return new WP_Error( 'nexus_seo_missing_property', 'Es ist noch keine Search-Console-Property hinterlegt.' );
	}

	if ( '' === $url ) {
		return new WP_Error( 'nexus_seo_invalid_url', 'Für die URL-Inspektion wurde keine gültige URL übergeben.' );
	}

	$cache_key = nexus_get_seo_cockpit_cache_key( 'inspection', [ $property, $url ] );
	$cached    = get_transient( $cache_key );

	if ( ! $force && is_array( $cached ) ) {
		return $cached;
	}

	$response = nexus_seo_cockpit_google_request(
		'POST',
		nexus_get_seo_cockpit_inspection_api_base_url() . '/urlInspection/index:inspect',
		[
			'inspectionUrl' => $url,
			'siteUrl'       => $property,
			'languageCode'  => 'de-DE',
		]
	);

	if ( is_wp_error( $response ) ) {
		return $response;
	}

	$result = isset( $response['inspectionResult'] ) && is_array( $response['inspectionResult'] ) ? $response['inspectionResult'] : [];
	$parsed = [
		'checked_at'          => current_time( 'timestamp' ),
		'inspected_url'       => $url,
		'verdict'             => (string) ( $result['indexStatusResult']['verdict'] ?? '' ),
		'coverage_state'      => (string) ( $result['indexStatusResult']['coverageState'] ?? '' ),
		'indexing_state'      => (string) ( $result['indexStatusResult']['indexingState'] ?? '' ),
		'page_fetch_state'    => (string) ( $result['indexStatusResult']['pageFetchState'] ?? '' ),
		'robots_txt_state'    => (string) ( $result['indexStatusResult']['robotsTxtState'] ?? '' ),
		'last_crawl_time'     => (string) ( $result['indexStatusResult']['lastCrawlTime'] ?? '' ),
		'google_canonical'    => (string) ( $result['indexStatusResult']['googleCanonical'] ?? '' ),
		'user_canonical'      => (string) ( $result['indexStatusResult']['userCanonical'] ?? '' ),
		'referring_urls'      => isset( $result['indexStatusResult']['referringUrls'] ) && is_array( $result['indexStatusResult']['referringUrls'] ) ? array_values( $result['indexStatusResult']['referringUrls'] ) : [],
		'sitemaps'            => isset( $result['indexStatusResult']['sitemap'] ) && is_array( $result['indexStatusResult']['sitemap'] ) ? array_values( $result['indexStatusResult']['sitemap'] ) : [],
		'raw'                 => $result,
	];

	set_transient( $cache_key, $parsed, 12 * HOUR_IN_SECONDS );

	return $parsed;
}

/**
 * Run one manual URL inspection and redirect back to the detail view.
 *
 * @return void
 */
function nexus_handle_seo_cockpit_inspect_action() {
	if ( ! nexus_current_user_can_manage_seo_cockpit() ) {
		wp_die( 'Nicht erlaubt.' );
	}

	check_admin_referer( 'nexus_seo_cockpit_inspect' );

	$url   = isset( $_POST['inspection_url'] ) ? sanitize_text_field( (string) wp_unslash( $_POST['inspection_url'] ) ) : '';
	$range = isset( $_POST['range'] ) ? absint( wp_unslash( $_POST['range'] ) ) : 28;
	$url   = nexus_normalize_seo_cockpit_url( $url );

	$result = nexus_get_seo_cockpit_url_inspection( $url, true );
	$notice = is_wp_error( $result ) ? 'inspection_failed' : 'inspection_success';

	wp_safe_redirect(
		nexus_get_seo_cockpit_detail_url(
			$url,
			[
				'range'            => $range,
				'nexus_seo_notice' => $notice,
			]
		)
	);
	exit;
}
add_action( 'admin_post_nexus_seo_cockpit_inspect', 'nexus_handle_seo_cockpit_inspect_action' );
