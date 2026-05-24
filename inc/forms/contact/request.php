<?php
/**
 * Contact form request protection and flash helpers.
 *
 * @package Hasimuener_Journal
 */


defined( 'ABSPATH' ) || exit;

/**
 * Einstellungen für Formularschutz und Drosselung.
 *
 * @return array{min_seconds:int,max_age:int,rate_window:int,max_links:int}
 */
function hp_get_contact_form_settings(): array {
	return [
		'min_seconds' => 4,
		'max_age'     => DAY_IN_SECONDS,
		'rate_window' => 90,
		'max_links'   => 3,
	];
}

/**
 * Erstellt den Prüf-Token des Kontaktformulars.
 */
function hp_get_contact_form_render_token( int $rendered_at ): string {
	return wp_hash( $rendered_at . '|hp-contact-form' );
}

/**
 * Erzeugt einen anonymisierten Rate-Limit-Schlüssel.
 */
function hp_get_contact_form_rate_key(): string {
	if ( is_user_logged_in() ) {
		return 'user_' . get_current_user_id();
	}

	$ip = '';

	if ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
		$ip = (string) wp_unslash( $_SERVER['REMOTE_ADDR'] );
	}

	if ( '' === $ip ) {
		return 'guest_' . md5( wp_get_session_token() ?: 'anonymous' );
	}

	return 'guest_' . md5( $ip );
}

/**
 * Speichert eine Kurzmitteilung für den nächsten Redirect.
 *
 * @param array<string, mixed> $payload Flash-Daten.
 */
function hp_store_contact_flash( array $payload ): string {
	$token = strtolower( wp_generate_password( 24, false, false ) );
	set_transient( 'hp_contact_flash_' . $token, $payload, 10 * MINUTE_IN_SECONDS );

	return $token;
}

/**
 * Holt Flash-Daten einmalig aus dem Redirect.
 *
 * @return array<string, mixed>
 */
function hp_consume_contact_flash(): array {
	$token = isset( $_GET['contact'] ) ? sanitize_key( (string) wp_unslash( $_GET['contact'] ) ) : '';

	if ( '' === $token ) {
		return [];
	}

	$key   = 'hp_contact_flash_' . $token;
	$flash = get_transient( $key );

	delete_transient( $key );

	return is_array( $flash ) ? $flash : [];
}

/**
 * Leitet mit Flash-Daten zurück auf die Kontaktseite.
 *
 * @param array<string, mixed> $payload Flash-Daten.
 */
function hp_redirect_contact_form( array $payload ): void {
	$token = hp_store_contact_flash( $payload );
	$url   = add_query_arg( 'contact', rawurlencode( $token ), hp_get_contact_page_url() );

	wp_safe_redirect( $url );
	exit;
}
