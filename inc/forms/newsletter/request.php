<?php
/**
 * Newsletter request, token, redirect, and flash helpers.
 *
 * @package Hasimuener_Journal
 */


defined( 'ABSPATH' ) || exit;

/**
 * Formular- und Missbrauchsschutz.
 *
 * @return array{min_seconds:int,max_age:int,rate_window:int}
 */
function hp_get_newsletter_form_settings(): array {
	return [
		'min_seconds' => 3,
		'max_age'     => DAY_IN_SECONDS,
		'rate_window' => 75,
	];
}

/**
 * Render-Token für das Formular.
 */
function hp_get_newsletter_form_render_token( int $rendered_at ): string {
	return wp_hash( $rendered_at . '|hp-newsletter-form' );
}

/**
 * Liefert die aktuelle URL für Rücksprünge.
 */
function hp_get_newsletter_current_url(): string {
	$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_unslash( $_SERVER['REQUEST_URI'] ) : '/';

	if ( '' === $request_uri || '/' !== $request_uri[0] ) {
		$request_uri = '/';
	}

	return remove_query_arg( [ 'newsletter' ], home_url( $request_uri ) );
}

/**
 * Validiert Redirect-Ziele auf die eigene Domain.
 */
function hp_get_newsletter_redirect_target( string $raw_url ): string {
	$fallback = home_url( '/' );
	$raw_url  = trim( $raw_url );

	if ( '' === $raw_url ) {
		return $fallback;
	}

	$validated = wp_validate_redirect( $raw_url, '' );

	if ( '' === $validated ) {
		return $fallback;
	}

	$home_host      = (string) wp_parse_url( home_url( '/' ), PHP_URL_HOST );
	$validated_host = (string) wp_parse_url( $validated, PHP_URL_HOST );

	if ( '' !== $validated_host && '' !== $home_host && $validated_host !== $home_host ) {
		return $fallback;
	}

	return remove_query_arg( [ 'newsletter' ], $validated );
}

/**
 * Anonymisierter Request-Fingerprint für Nachweis und Schutz.
 *
 * @return array{ip_hash:string,user_agent_hash:string}
 */
function hp_get_newsletter_request_fingerprint(): array {
	$ip = '';

	if ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
		$ip = trim( (string) wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
	}

	$user_agent = '';

	if ( ! empty( $_SERVER['HTTP_USER_AGENT'] ) ) {
		$user_agent = trim( (string) wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) );
	}

	return [
		'ip_hash'         => '' !== $ip ? hash( 'sha256', $ip ) : '',
		'user_agent_hash' => '' !== $user_agent ? hash( 'sha256', $user_agent ) : '',
	];
}

/**
 * Rate-Limit-Schlüssel.
 */
function hp_get_newsletter_rate_key( string $email = '' ): string {
	$fingerprint = hp_get_newsletter_request_fingerprint();
	$seed        = $fingerprint['ip_hash'];

	if ( '' === $seed ) {
		$seed = wp_get_session_token() ?: 'anonymous';
	}

	return 'hp_newsletter_rate_' . md5( strtolower( trim( $email ) ) . '|' . $seed );
}

/**
 * Erzeugt kryptisch ausreichend zufällige Tokens.
 */
function hp_generate_newsletter_token(): string {
	return hash( 'sha256', wp_generate_password( 64, true, true ) . '|' . microtime( true ) . '|' . wp_rand() );
}

/**
 * Flash-Daten speichern.
 *
 * @param array<string, mixed> $payload Kurzmitteilung.
 */
function hp_store_newsletter_flash( array $payload ): string {
	$token = strtolower( wp_generate_password( 24, false, false ) );
	set_transient( 'hp_newsletter_flash_' . $token, $payload, 10 * MINUTE_IN_SECONDS );

	return $token;
}

/**
 * Flash-Daten einmalig laden.
 *
 * @return array<string, mixed>
 */
function hp_consume_newsletter_flash(): array {
	$token = isset( $_GET['newsletter'] ) ? sanitize_key( (string) wp_unslash( $_GET['newsletter'] ) ) : '';

	if ( '' === $token ) {
		return [];
	}

	$key   = 'hp_newsletter_flash_' . $token;
	$flash = get_transient( $key );

	delete_transient( $key );

	return is_array( $flash ) ? $flash : [];
}

/**
 * Flash-Daten mit statischem Cache bereitstellen.
 *
 * @return array<string, mixed>
 */
function hp_get_newsletter_flash(): array {
	static $flash = null;

	if ( null === $flash ) {
		$flash = hp_consume_newsletter_flash();
	}

	return $flash;
}

/**
 * Redirect mit Flash-Daten.
 *
 * @param array<string, mixed> $payload Meldung.
 */
function hp_redirect_newsletter( string $target_url, array $payload ): void {
	$token = hp_store_newsletter_flash( $payload );
	$url   = add_query_arg( 'newsletter', rawurlencode( $token ), hp_get_newsletter_redirect_target( $target_url ) );

	wp_safe_redirect( $url );
	exit;
}
