<?php
/**
 * Contact page and contact form.
 *
 * Stable loader for the contact feature. Local configuration remains in
 * inc/contact-local.php; public hp_* functions live in inc/forms/contact/.
 *
 * @package Hasimuener_Journal
 * @since   6.3.0
 */

defined( 'ABSPATH' ) || exit;

$hp_contact_local_config = __DIR__ . '/contact-local.php';

if ( file_exists( $hp_contact_local_config ) ) {
	require_once $hp_contact_local_config;
}

$hp_contact_dir = __DIR__ . '/forms/contact';
$hp_contact_modules = [
	'config.php',
	'page.php',
	'request.php',
	'mail.php',
	'handlers.php',
];

foreach ( $hp_contact_modules as $hp_contact_module ) {
	require_once $hp_contact_dir . '/' . $hp_contact_module;
}

unset( $hp_contact_local_config, $hp_contact_dir, $hp_contact_modules, $hp_contact_module );
