<?php
/**
 * SEO Cockpit core bootstrap, capabilities and settings.
 *
 * @package Hasimuener_Journal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return the admin slug for the SEO cockpit.
 *
 * @return string
 */
function nexus_get_seo_cockpit_menu_slug() {
	return 'nexus-seo-cockpit';
}

/**
 * Return the settings option name for the SEO cockpit.
 *
 * @return string
 */
function nexus_get_seo_cockpit_option_name() {
	return 'nexus_seo_cockpit_settings';
}

/**
 * Return the token storage option name for the SEO cockpit.
 *
 * @return string
 */
function nexus_get_seo_cockpit_token_option_name() {
	return 'nexus_seo_cockpit_tokens';
}

/**
 * Return the runtime option name for sync diagnostics.
 *
 * @return string
 */
function nexus_get_seo_cockpit_runtime_option_name() {
	return 'nexus_seo_cockpit_runtime';
}

/**
 * Return the option name for cache versioning.
 *
 * @return string
 */
function nexus_get_seo_cockpit_cache_version_option_name() {
	return 'nexus_seo_cockpit_cache_version';
}

/**
 * Return the SEO cockpit view capability.
 *
 * @return string
 */
function nexus_get_seo_cockpit_view_cap() {
	return 'view_seo_cockpit';
}

/**
 * Return the SEO cockpit management capability.
 *
 * @return string
 */
function nexus_get_seo_cockpit_manage_cap() {
	return 'manage_seo_cockpit';
}

/**
 * Determine whether the current user may view the cockpit.
 *
 * @return bool
 */
function nexus_current_user_can_view_seo_cockpit() {
	return current_user_can( nexus_get_seo_cockpit_view_cap() ) || current_user_can( nexus_get_seo_cockpit_manage_cap() ) || current_user_can( 'manage_options' );
}

/**
 * Determine whether the current user may manage the cockpit.
 *
 * @return bool
 */
function nexus_current_user_can_manage_seo_cockpit() {
	return current_user_can( nexus_get_seo_cockpit_manage_cap() ) || current_user_can( 'manage_options' );
}

/**
 * Ensure cockpit capabilities are present on core roles.
 *
 * @return void
 */
function nexus_register_seo_cockpit_capabilities() {
	$administrator = get_role( 'administrator' );
	if ( $administrator instanceof WP_Role ) {
		$administrator->add_cap( nexus_get_seo_cockpit_view_cap() );
		$administrator->add_cap( nexus_get_seo_cockpit_manage_cap() );
	}

	$editor = get_role( 'editor' );
	if ( $editor instanceof WP_Role ) {
		$editor->add_cap( nexus_get_seo_cockpit_view_cap() );
	}
}
add_action( 'admin_init', 'nexus_register_seo_cockpit_capabilities' );
add_action( 'after_switch_theme', 'nexus_register_seo_cockpit_capabilities' );

/**
 * Return the Search Console OAuth scope used by the cockpit.
 *
 * @return string
 */
function nexus_get_seo_cockpit_scope() {
	return 'https://www.googleapis.com/auth/webmasters.readonly';
}

/**
 * Return the Search Console REST API base URL.
 *
 * @return string
 */
function nexus_get_seo_cockpit_api_base_url() {
	return 'https://www.googleapis.com/webmasters/v3';
}

/**
 * Return the URL Inspection API base URL.
 *
 * @return string
 */
function nexus_get_seo_cockpit_inspection_api_base_url() {
	return 'https://searchconsole.googleapis.com/v1';
}

/**
 * Return the first non-empty constant value from a list of names.
 *
 * @param array<int, string> $names Constant names in lookup order.
 * @return string
 */
function nexus_get_seo_cockpit_constant_value( $names ) {
	foreach ( (array) $names as $name ) {
		if ( defined( (string) $name ) && constant( (string) $name ) ) {
			return (string) constant( (string) $name );
		}
	}

	return '';
}

/**
 * Return the Search Console OAuth redirect URI.
 *
 * @return string
 */
function nexus_get_seo_cockpit_redirect_uri() {
	$constant = nexus_get_seo_cockpit_constant_value( [ 'HP_GSC_REDIRECT_URI', 'NEXUS_GSC_REDIRECT_URI' ] );

	if ( '' !== $constant ) {
		return esc_url_raw( $constant );
	}

	return admin_url( 'admin-post.php?action=nexus_seo_cockpit_oauth_callback' );
}

/**
 * Return persisted cockpit settings.
 *
 * @return array<string, string>
 */
function nexus_get_seo_cockpit_settings() {
	$settings = get_option( nexus_get_seo_cockpit_option_name(), [] );

	if ( ! is_array( $settings ) ) {
		$settings = [];
	}

	return wp_parse_args(
		$settings,
		[
			'client_id'      => '',
			'client_secret'  => '',
			'property'       => '',
			'refresh_window' => '12',
		]
	);
}

/**
 * Return one cockpit setting with optional constant override.
 *
 * @param string $key     Setting key.
 * @param string $default Default value.
 * @return string
 */
function nexus_get_seo_cockpit_setting( $key, $default = '' ) {
	$key      = sanitize_key( (string) $key );
	$settings = nexus_get_seo_cockpit_settings();
	$map      = [
		'client_id'     => [ 'HP_GSC_CLIENT_ID', 'NEXUS_GSC_CLIENT_ID' ],
		'client_secret' => [ 'HP_GSC_CLIENT_SECRET', 'NEXUS_GSC_CLIENT_SECRET' ],
		'property'      => [ 'HP_GSC_PROPERTY', 'NEXUS_GSC_PROPERTY' ],
	];

	if ( isset( $map[ $key ] ) ) {
		$constant = nexus_get_seo_cockpit_constant_value( $map[ $key ] );
		if ( '' !== $constant ) {
			return $constant;
		}
	}

	return isset( $settings[ $key ] ) ? (string) $settings[ $key ] : (string) $default;
}

/**
 * Return whether one setting comes from a constant or the database option.
 *
 * @param string $key Setting key.
 * @return string
 */
function nexus_get_seo_cockpit_setting_source( $key ) {
	$key = sanitize_key( (string) $key );
	$map = [
		'client_id'     => [ 'HP_GSC_CLIENT_ID', 'NEXUS_GSC_CLIENT_ID' ],
		'client_secret' => [ 'HP_GSC_CLIENT_SECRET', 'NEXUS_GSC_CLIENT_SECRET' ],
		'property'      => [ 'HP_GSC_PROPERTY', 'NEXUS_GSC_PROPERTY' ],
	];

	if ( isset( $map[ $key ] ) && '' !== nexus_get_seo_cockpit_constant_value( $map[ $key ] ) ) {
		return 'Konstante';
	}

	return 'Option';
}

/**
 * Return the effective Search Console config.
 *
 * @return array<string, string>
 */
function nexus_get_seo_cockpit_search_console_config() {
	return [
		'client_id'     => trim( nexus_get_seo_cockpit_setting( 'client_id' ) ),
		'client_secret' => trim( nexus_get_seo_cockpit_setting( 'client_secret' ) ),
		'property'      => trim( nexus_get_seo_cockpit_setting( 'property' ) ),
		'redirect_uri'  => nexus_get_seo_cockpit_redirect_uri(),
		'scope'         => nexus_get_seo_cockpit_scope(),
	];
}

/**
 * Determine whether the cockpit has usable Search Console credentials.
 *
 * @return bool
 */
function nexus_has_seo_cockpit_search_console_credentials() {
	$config = nexus_get_seo_cockpit_search_console_config();

	return '' !== $config['client_id'] && '' !== $config['client_secret'] && '' !== $config['redirect_uri'];
}

/**
 * Return a compact setup-state summary for the cockpit.
 *
 * @return array<string, mixed>
 */
function nexus_get_seo_cockpit_setup_state() {
	$config       = nexus_get_seo_cockpit_search_console_config();
	$tokens       = nexus_get_seo_cockpit_tokens();
	$is_connected = '' !== (string) ( $tokens['access_token'] ?? '' );
	$missing      = [];

	if ( '' === $config['property'] ) {
		$missing[] = 'Property';
	}

	if ( '' === $config['client_id'] ) {
		$missing[] = 'Client ID';
	}

	if ( '' === $config['client_secret'] ) {
		$missing[] = 'Client Secret';
	}

	return [
		'config'            => $config,
		'is_connected'      => $is_connected,
		'has_property'      => '' !== $config['property'],
		'has_client_id'     => '' !== $config['client_id'],
		'has_secret'        => '' !== $config['client_secret'],
		'is_ready'          => empty( $missing ) && $is_connected,
		'missing'           => $missing,
		'can_view'          => nexus_current_user_can_view_seo_cockpit(),
		'can_manage'        => nexus_current_user_can_manage_seo_cockpit(),
	];
}

/**
 * Return the stored token payload.
 *
 * @return array<string, mixed>
 */
function nexus_get_seo_cockpit_tokens() {
	$tokens = get_option( nexus_get_seo_cockpit_token_option_name(), [] );

	return is_array( $tokens ) ? $tokens : [];
}

/**
 * Return the stored runtime payload.
 *
 * @return array<string, mixed>
 */
function nexus_get_seo_cockpit_runtime() {
	$runtime = get_option( nexus_get_seo_cockpit_runtime_option_name(), [] );

	return is_array( $runtime ) ? $runtime : [];
}

/**
 * Persist the Search Console token payload.
 *
 * @param array<string, mixed> $tokens Token payload.
 * @return void
 */
function nexus_update_seo_cockpit_tokens( $tokens ) {
	update_option( nexus_get_seo_cockpit_token_option_name(), $tokens, false );
}

/**
 * Persist runtime sync diagnostics.
 *
 * @param array<string, mixed> $runtime Runtime payload.
 * @return void
 */
function nexus_update_seo_cockpit_runtime( $runtime ) {
	update_option( nexus_get_seo_cockpit_runtime_option_name(), $runtime, false );
}

/**
 * Return the current cache version.
 *
 * @return int
 */
function nexus_get_seo_cockpit_cache_version() {
	return max( 1, absint( get_option( nexus_get_seo_cockpit_cache_version_option_name(), 1 ) ) );
}

/**
 * Bump the SEO cockpit cache version to invalidate old transients.
 *
 * @return int
 */
function nexus_bump_seo_cockpit_cache_version() {
	$version = nexus_get_seo_cockpit_cache_version() + 1;
	update_option( nexus_get_seo_cockpit_cache_version_option_name(), $version, false );

	return $version;
}

/**
 * Build a versioned cache key for cockpit transients.
 *
 * @param string               $type  Cache bucket type.
 * @param array<int, string|int> $parts Optional cache key fragments.
 * @return string
 */
function nexus_get_seo_cockpit_cache_key( $type, $parts = [] ) {
	$fragments = array_map(
		static function ( $part ) {
			return (string) $part;
		},
		(array) $parts
	);

	return sprintf(
		'nexus_seo_%1$s_%2$d_%3$s',
		sanitize_key( (string) $type ),
		nexus_get_seo_cockpit_cache_version(),
		md5( implode( '|', $fragments ) )
	);
}

/**
 * Return allowed range presets for the cockpit.
 *
 * @return array<int>
 */
function nexus_get_seo_cockpit_allowed_ranges() {
	return [ 7, 28, 90 ];
}

/**
 * Return per-report row caps and paging defaults.
 *
 * @return array<string, array<string, int|bool>>
 */
function nexus_get_seo_cockpit_row_caps() {
	return [
		'top_pages'       => [
			'limit'     => 20,
			'page_size' => 100,
			'max_pages' => 1,
			'paginate'  => false,
		],
		'top_queries'     => [
			'limit'     => 20,
			'page_size' => 100,
			'max_pages' => 1,
			'paginate'  => false,
		],
		'top_devices'     => [
			'limit'     => 10,
			'page_size' => 25,
			'max_pages' => 1,
			'paginate'  => false,
		],
		'page_rows'       => [
			'limit'     => 100,
			'page_size' => 100,
			'max_pages' => 2,
			'paginate'  => true,
		],
		'query_page_rows' => [
			'limit'     => 400,
			'page_size' => 200,
			'max_pages' => 3,
			'paginate'  => true,
		],
		'detail_queries'  => [
			'limit'     => 50,
			'page_size' => 100,
			'max_pages' => 1,
			'paginate'  => false,
		],
		'detail_devices'  => [
			'limit'     => 10,
			'page_size' => 25,
			'max_pages' => 1,
			'paginate'  => false,
		],
	];
}

/**
 * Return one report cap config by bucket.
 *
 * @param string $bucket Bucket key.
 * @return array<string, int|bool>
 */
function nexus_get_seo_cockpit_row_cap( $bucket ) {
	$caps = nexus_get_seo_cockpit_row_caps();

	return isset( $caps[ $bucket ] ) && is_array( $caps[ $bucket ] )
		? $caps[ $bucket ]
		: [
			'limit'     => 25,
			'page_size' => 100,
			'max_pages' => 1,
			'paginate'  => false,
		];
}

/**
 * Return the requested range in days from the current admin request.
 *
 * @return int
 */
function nexus_get_seo_cockpit_requested_range_days() {
	$range = isset( $_GET['range'] ) ? absint( wp_unslash( $_GET['range'] ) ) : 28;

	if ( ! in_array( $range, nexus_get_seo_cockpit_allowed_ranges(), true ) ) {
		$range = 28;
	}

	return $range;
}

/**
 * Normalize a URL to one canonical cockpit string.
 *
 * @param string $url Raw URL.
 * @return string
 */
function nexus_normalize_seo_cockpit_url( $url ) {
	$url = trim( (string) $url );

	if ( '' === $url ) {
		return '';
	}

	if ( 0 === strpos( $url, '/' ) ) {
		$url = home_url( $url );
	}

	$parts = wp_parse_url( $url );
	if ( ! is_array( $parts ) ) {
		return '';
	}

	$home_scheme = (string) wp_parse_url( home_url( '/' ), PHP_URL_SCHEME );
	$scheme      = strtolower( (string) ( $parts['scheme'] ?? $home_scheme ?: 'https' ) );
	$host        = strtolower( (string) ( $parts['host'] ?? wp_parse_url( home_url( '/' ), PHP_URL_HOST ) ) );
	$path        = isset( $parts['path'] ) ? '/' . ltrim( (string) $parts['path'], '/' ) : '/';

	if ( '/' !== $path ) {
		$path = trailingslashit( $path );
	}

	$normalized = $scheme . '://' . $host . $path;

	if ( ! empty( $parts['query'] ) ) {
		$normalized .= '?' . (string) $parts['query'];
	}

	return $normalized;
}

/**
 * Normalize a search query for grouping heuristics.
 *
 * @param string $query Raw query string.
 * @return string
 */
function nexus_normalize_seo_cockpit_query( $query ) {
	$query = trim( mb_strtolower( wp_strip_all_tags( (string) $query ) ) );
	$query = preg_replace( '/[^\p{L}\p{N}\s]+/u', ' ', $query );
	$query = preg_replace( '/\s+/u', ' ', (string) $query );

	return trim( (string) $query );
}

/**
 * Return the currently selected cockpit detail URL.
 *
 * @return string
 */
function nexus_get_seo_cockpit_selected_detail_url() {
	$url = isset( $_GET['url'] ) ? (string) wp_unslash( $_GET['url'] ) : '';

	return nexus_normalize_seo_cockpit_url( $url );
}

/**
 * Return a URL for cockpit actions.
 *
 * @param string $action Action key.
 * @param array  $args   Optional query args.
 * @return string
 */
function nexus_get_seo_cockpit_admin_action_url( $action, $args = [] ) {
	$args = array_merge(
		[
			'action' => $action,
		],
		(array) $args
	);

	return add_query_arg( $args, admin_url( 'admin-post.php' ) );
}

/**
 * Return the dashboard URL with optional arguments.
 *
 * @param array $args Optional query args.
 * @return string
 */
function nexus_get_seo_cockpit_dashboard_url( $args = [] ) {
	$url = admin_url( 'admin.php?page=' . nexus_get_seo_cockpit_menu_slug() );

	if ( ! empty( $args ) ) {
		$url = add_query_arg( $args, $url );
	}

	return $url;
}

/**
 * Return the settings page URL.
 *
 * @param array $args Optional query args.
 * @return string
 */
function nexus_get_seo_cockpit_settings_url( $args = [] ) {
	$url = admin_url( 'admin.php?page=nexus-seo-cockpit-settings' );

	if ( ! empty( $args ) ) {
		$url = add_query_arg( $args, $url );
	}

	return $url;
}

/**
 * Return the detail view URL for one cockpit URL.
 *
 * @param string $url  Frontend URL.
 * @param array  $args Optional query args.
 * @return string
 */
function nexus_get_seo_cockpit_detail_url( $url, $args = [] ) {
	$url  = nexus_normalize_seo_cockpit_url( $url );
	$args = array_merge(
		[
			'url'   => $url,
			'range' => nexus_get_seo_cockpit_requested_range_days(),
		],
		$args
	);

	return nexus_get_seo_cockpit_dashboard_url( $args );
}

/**
 * Register the SEO cockpit admin menu.
 *
 * @return void
 */
function nexus_register_seo_cockpit_menu() {
	add_menu_page(
		'SEO Cockpit',
		'SEO Cockpit',
		nexus_get_seo_cockpit_view_cap(),
		nexus_get_seo_cockpit_menu_slug(),
		'nexus_render_seo_cockpit_dashboard',
		'dashicons-chart-area',
		59
	);

	add_submenu_page(
		nexus_get_seo_cockpit_menu_slug(),
		'SEO Cockpit',
		'Übersicht',
		nexus_get_seo_cockpit_view_cap(),
		nexus_get_seo_cockpit_menu_slug(),
		'nexus_render_seo_cockpit_dashboard'
	);

	add_submenu_page(
		nexus_get_seo_cockpit_menu_slug(),
		'SEO Cockpit Einstellungen',
		'Einstellungen',
		nexus_get_seo_cockpit_manage_cap(),
		'nexus-seo-cockpit-settings',
		'nexus_render_seo_cockpit_settings_page'
	);
}
add_action( 'admin_menu', 'nexus_register_seo_cockpit_menu' );

/**
 * Register the cockpit settings group.
 *
 * @return void
 */
function nexus_register_seo_cockpit_settings() {
	register_setting(
		'nexus_seo_cockpit_settings',
		nexus_get_seo_cockpit_option_name(),
		[
			'type'              => 'array',
			'sanitize_callback' => 'nexus_sanitize_seo_cockpit_settings',
			'default'           => [],
		]
	);
}
add_action( 'admin_init', 'nexus_register_seo_cockpit_settings' );

/**
 * Sanitize the cockpit settings payload.
 *
 * @param mixed $settings Raw settings payload.
 * @return array<string, string>
 */
function nexus_sanitize_seo_cockpit_settings( $settings ) {
	$settings      = is_array( $settings ) ? $settings : [];
	$previous      = nexus_get_seo_cockpit_settings();
	$refresh_hours = (string) max( 1, min( 24, absint( $settings['refresh_window'] ?? 12 ) ) );
	$sanitized     = [
		'client_id'      => sanitize_text_field( (string) ( $settings['client_id'] ?? '' ) ),
		'client_secret'  => sanitize_text_field( (string) ( $settings['client_secret'] ?? '' ) ),
		'property'       => sanitize_text_field( (string) ( $settings['property'] ?? '' ) ),
		'refresh_window' => $refresh_hours,
	];

	if ( (string) ( $previous['refresh_window'] ?? '' ) !== $refresh_hours && function_exists( 'nexus_maybe_reschedule_seo_cockpit_refresh_event' ) ) {
		nexus_maybe_reschedule_seo_cockpit_refresh_event( true, $refresh_hours );
	}

	nexus_add_seo_cockpit_settings_feedback( $sanitized );

	return $sanitized;
}

/**
 * Add an explicit connection-status notice after saving cockpit settings.
 *
 * @param array<string, string> $settings Sanitized settings payload.
 * @return void
 */
function nexus_add_seo_cockpit_settings_feedback( $settings ) {
	$option_name = nexus_get_seo_cockpit_option_name();
	$effective   = [
		'property'      => nexus_get_seo_cockpit_setting( 'property' ),
		'client_id'     => nexus_get_seo_cockpit_setting( 'client_id' ),
		'client_secret' => nexus_get_seo_cockpit_setting( 'client_secret' ),
	];
	$missing     = [];

	if ( '' === $effective['property'] ) {
		$missing[] = 'Property';
	}

	if ( '' === $effective['client_id'] ) {
		$missing[] = 'Client ID';
	}

	if ( '' === $effective['client_secret'] ) {
		$missing[] = 'Client Secret';
	}

	if ( ! empty( $missing ) ) {
		add_settings_error(
			$option_name,
			'nexus_seo_settings_missing',
			sprintf(
				'Einstellungen gespeichert. Es fehlen noch: %s.',
				implode( ', ', $missing )
			),
			'warning'
		);

		return;
	}

	$tokens = nexus_get_seo_cockpit_tokens();
	if ( '' === (string) ( $tokens['access_token'] ?? '' ) ) {
		add_settings_error(
			$option_name,
			'nexus_seo_settings_not_connected',
			'Einstellungen gespeichert. Es besteht noch keine Google-Verbindung. Klicke in der Übersicht auf "Mit Google verbinden".',
			'warning'
		);

		return;
	}

	if ( function_exists( 'nexus_delete_seo_cockpit_snapshot_cache' ) ) {
		nexus_delete_seo_cockpit_snapshot_cache();
	}

	$sites = function_exists( 'nexus_get_seo_cockpit_sites' ) ? nexus_get_seo_cockpit_sites( true ) : [];
	if ( is_wp_error( $sites ) ) {
		add_settings_error(
			$option_name,
			'nexus_seo_settings_connection_error',
			sprintf(
				'Einstellungen gespeichert, aber die Search-Console-Verbindung ist aktuell nicht nutzbar: %s',
				$sites->get_error_message()
			),
			'error'
		);

		return;
	}

	add_settings_error(
		$option_name,
		'nexus_seo_settings_connected',
		'Einstellungen gespeichert. Die Search Console ist verbunden und abrufbar.',
		'success'
	);
}

/**
 * Enqueue admin styles for the SEO cockpit.
 *
 * @param string $hook Current admin hook.
 * @return void
 */
function nexus_enqueue_seo_cockpit_admin_assets( $hook ) {
	$is_seo_screen      = false !== strpos( (string) $hook, nexus_get_seo_cockpit_menu_slug() );
	$is_dashboard_index = 'index.php' === (string) $hook;

	if ( ! $is_seo_screen && ! $is_dashboard_index ) {
		return;
	}

	$path = get_stylesheet_directory() . '/assets/css/seo-cockpit-admin.css';
	if ( file_exists( $path ) ) {
		wp_enqueue_style(
			'hp-seo-cockpit-admin',
			get_stylesheet_directory_uri() . '/assets/css/seo-cockpit-admin.css',
			[],
			filemtime( $path )
		);
	}
}
add_action( 'admin_enqueue_scripts', 'nexus_enqueue_seo_cockpit_admin_assets' );
