<?php
/**
 * Theme bootstrap — Hasimuener Journal
 *
 * Loads all modules declared in inc/manifest.php. Business logic belongs in
 * the modules themselves; this file owns only module discovery and loading.
 *
 * @package Hasimuener_Journal
 */

defined( 'ABSPATH' ) || exit;

$hp_inc_dir  = __DIR__;
$hp_manifest = require $hp_inc_dir . '/manifest.php';

if ( ! is_array( $hp_manifest ) ) {
	throw new RuntimeException( 'Hasimuener Journal module manifest must return an array.' );
}

foreach ( $hp_manifest as $hp_module ) {
	if ( ! is_string( $hp_module ) || '' === $hp_module ) {
		throw new RuntimeException( 'Hasimuener Journal module manifest contains an invalid entry.' );
	}

	$hp_module_path = $hp_inc_dir . '/' . ltrim( $hp_module, '/' );

	if ( ! file_exists( $hp_module_path ) ) {
		throw new RuntimeException( sprintf( 'Hasimuener Journal module not found: %s', $hp_module ) );
	}

	require_once $hp_module_path;
}

unset( $hp_inc_dir, $hp_manifest, $hp_module, $hp_module_path );
