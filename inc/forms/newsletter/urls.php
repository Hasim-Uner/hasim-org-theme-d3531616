<?php
/**
 * Newsletter public action URLs.
 *
 * @package Hasimuener_Journal
 */


defined( 'ABSPATH' ) || exit;

/**
 * Bestätigungs-Link.
 */
function hp_get_newsletter_confirm_url( string $token ): string {
	return add_query_arg(
		[
			'action' => 'hp_confirm_newsletter',
			'token'  => rawurlencode( $token ),
		],
		admin_url( 'admin-post.php' )
	);
}

/**
 * Abmeldelink.
 */
function hp_get_newsletter_unsubscribe_url( string $token ): string {
	return add_query_arg(
		[
			'action' => 'hp_unsubscribe_newsletter',
			'token'  => rawurlencode( $token ),
		],
		admin_url( 'admin-post.php' )
	);
}

