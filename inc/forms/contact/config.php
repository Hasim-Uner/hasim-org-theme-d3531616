<?php
/**
 * Contact config and value helpers.
 *
 * @package Hasimuener_Journal
 */


defined( 'ABSPATH' ) || exit;

/**
 * Liefert die primäre Kontaktadresse.
 */
function hp_get_contact_email(): string {
	return 'hallo@hasimuener.org';
}

/**
 * Liefert die Versand-Absenderadresse (in Brevo verifiziert).
 * Vorübergehend hallo@hasimuener.de, bis hasimuener.org in Brevo verifiziert ist.
 */
function hp_get_contact_sender_email(): string {
	return 'hallo@hasimuener.de';
}

/**
 * Öffentlicher Titel der Kontaktseite.
 */
function hp_get_contact_page_title(): string {
	return 'Anfragen & Zusammenarbeit';
}

/**
 * Auswahloptionen für die Art der Anfrage.
 *
 * @return array<string, string>
 */
function hp_get_contact_inquiry_type_options(): array {
	return [
		'editorial'   => 'Redaktionelle Anfrage',
		'essay'       => 'Gastbeitrag / Essay',
		'interview'   => 'Interview / Gespräch / Vortrag',
		'cooperation' => 'Kooperation',
		'writing'     => 'Schreibprojekt / Textanfrage',
		'other'       => 'Sonstiges',
	];
}

/**
 * Liefert die lesbare Bezeichnung eines Anfragetyps.
 */
function hp_get_contact_inquiry_type_label( string $inquiry_type ): string {
	$options = hp_get_contact_inquiry_type_options();

	if ( isset( $options[ $inquiry_type ] ) ) {
		return $options[ $inquiry_type ];
	}

	return $options['other'];
}

/**
 * Normalisiert eingegebene Websites oder Links.
 */
function hp_normalize_contact_website_url( string $url ): string {
	$url = trim( $url );

	if ( '' === $url ) {
		return '';
	}

	if ( ! preg_match( '#^[a-z][a-z0-9+\-.]*://#i', $url ) ) {
		$url = 'https://' . ltrim( $url, '/' );
	}

	return esc_url_raw( $url, [ 'http', 'https' ] );
}

/**
 * Baut eine knappe interne Betreffzeile für neue Anfragen.
 *
 * @param array<string, string> $fields Validierte Formularfelder.
 */
function hp_get_contact_submission_subject( array $fields ): string {
	$subject = hp_get_contact_inquiry_type_label( (string) ( $fields['inquiry_type'] ?? '' ) );

	if ( ! empty( $fields['organization'] ) ) {
		$subject .= ' - ' . trim( (string) $fields['organization'] );
	}

	if ( function_exists( 'mb_substr' ) ) {
		return (string) mb_substr( $subject, 0, 190 );
	}

	return substr( $subject, 0, 190 );
}

/**
 * Liefert den optionalen Brevo API-Key.
 */
function hp_get_brevo_api_key(): string {
	$key = '';

	if ( defined( 'HP_BREVO_API_KEY' ) && is_string( HP_BREVO_API_KEY ) ) {
		$key = trim( HP_BREVO_API_KEY );
	} else {
		$env_key = getenv( 'HP_BREVO_API_KEY' );
		$key     = is_string( $env_key ) ? trim( $env_key ) : '';
	}

	if ( 0 !== strpos( $key, 'xkeysib-' ) ) {
		return '';
	}

	return $key;
}

/**
 * Liefert den optionalen Brevo SMTP-Login.
 */
function hp_get_brevo_smtp_login(): string {
	if ( defined( 'HP_BREVO_SMTP_LOGIN' ) && is_string( HP_BREVO_SMTP_LOGIN ) ) {
		return trim( HP_BREVO_SMTP_LOGIN );
	}

	$login = getenv( 'HP_BREVO_SMTP_LOGIN' );

	return is_string( $login ) ? trim( $login ) : '';
}

/**
 * Liefert den optionalen Brevo SMTP-Key.
 */
function hp_get_brevo_smtp_key(): string {
	$key = '';

	if ( defined( 'HP_BREVO_SMTP_KEY' ) && is_string( HP_BREVO_SMTP_KEY ) ) {
		$key = trim( HP_BREVO_SMTP_KEY );
	} else {
		$env_key = getenv( 'HP_BREVO_SMTP_KEY' );
		$key     = is_string( $env_key ) ? trim( $env_key ) : '';
	}

	if ( 0 !== strpos( $key, 'xsmtpsib-' ) ) {
		return '';
	}

	return $key;
}

/**
 * Prüft, ob Brevo als Versandweg verfügbar ist.
 */
function hp_has_brevo_api_key(): bool {
	return '' !== hp_get_brevo_api_key();
}

/**
 * Prüft, ob Brevo-SMTP vollständig konfiguriert ist.
 */
function hp_has_brevo_smtp_config(): bool {
	return '' !== hp_get_brevo_smtp_login() && '' !== hp_get_brevo_smtp_key();
}

/**
 * Liefert einen API-tauglichen Sendernamen.
 */
function hp_get_contact_brevo_sender_name(): string {
	return 'Hasim Uener';
}
