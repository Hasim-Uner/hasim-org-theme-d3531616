<?php
/**
 * SEO Cockpit bootstrap loader.
 *
 * @package Hasimuener_Journal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// SEO Cockpit ist ein reines Admin-Dashboard — nicht im Frontend laden.
if ( ! is_admin() && ! wp_doing_ajax() && ! ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
	return;
}

$nexus_seo_cockpit_modules = [
	'seo-cockpit-core.php',
	'seo-cockpit-api.php',
	'seo-cockpit-koko.php',
	'seo-cockpit-links.php',
	'seo-cockpit-leads.php',
	'seo-cockpit-sync.php',
	'seo-cockpit-insights.php',
	'seo-cockpit-diagnostics.php',
	'seo-cockpit-ui.php',
];

foreach ( $nexus_seo_cockpit_modules as $nexus_seo_cockpit_module ) {
	$nexus_seo_cockpit_path = __DIR__ . '/' . $nexus_seo_cockpit_module;

	if ( file_exists( $nexus_seo_cockpit_path ) ) {
		require_once $nexus_seo_cockpit_path;
	}
}

unset( $nexus_seo_cockpit_modules, $nexus_seo_cockpit_module, $nexus_seo_cockpit_path );
