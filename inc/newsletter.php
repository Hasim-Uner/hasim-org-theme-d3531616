<?php
/**
 * Newsletter-Opt-in without plugin.
 *
 * Stable loader for the newsletter feature. The public hp_* functions remain
 * in the included files under inc/forms/newsletter/.
 *
 * @package Hasimuener_Journal
 * @since   6.4.0
 */

defined( 'ABSPATH' ) || exit;

$hp_newsletter_dir = __DIR__ . '/forms/newsletter';
$hp_newsletter_modules = [
	'config.php',
	'install.php',
	'request.php',
	'subscribers.php',
	'urls.php',
	'mail-templates.php',
	'mailer.php',
	'handlers.php',
	'queries-cleanup.php',
	'render.php',
	'admin.php',
];

foreach ( $hp_newsletter_modules as $hp_newsletter_module ) {
	require_once $hp_newsletter_dir . '/' . $hp_newsletter_module;
}

unset( $hp_newsletter_dir, $hp_newsletter_modules, $hp_newsletter_module );
