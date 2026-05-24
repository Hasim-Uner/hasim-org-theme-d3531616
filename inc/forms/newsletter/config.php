<?php
/**
 * Newsletter config and value helpers.
 *
 * @package Hasimuener_Journal
 */


defined( 'ABSPATH' ) || exit;

/**
 * Bezeichnung des Angebots.
 */
function hp_get_newsletter_label(): string {
	return 'Neue Texte per E-Mail';
}

/**
 * Liefert die Ziel-URL der Newsletter-Verlinkung.
 */
function hp_get_newsletter_anchor_url(): string {
	return home_url( '/#newsletter-signup' );
}

/**
 * Primäre Kontaktadresse für Rückfragen.
 */
function hp_get_newsletter_contact_email(): string {
	if ( function_exists( 'hp_get_contact_email' ) ) {
		return hp_get_contact_email();
	}

	return (string) get_option( 'admin_email' );
}

/**
 * Versand-Absenderadresse (in Brevo verifiziert).
 * Vorübergehend hallo@hasimuener.de, bis hasimuener.org in Brevo verifiziert ist.
 */
function hp_get_newsletter_sender_email(): string {
	if ( function_exists( 'hp_get_contact_sender_email' ) ) {
		return hp_get_contact_sender_email();
	}

	return 'hallo@hasimuener.de';
}

/**
 * Zustellungsfreundlicher Absendername.
 */
function hp_get_newsletter_sender_name(): string {
	if ( function_exists( 'hp_get_contact_mail_sender_name' ) ) {
		return hp_get_contact_mail_sender_name();
	}

	return 'Hasim Uener';
}

/**
 * X-Profil als sekundärer Kanal.
 */
function hp_get_newsletter_x_url(): string {
	return 'https://x.com/_0239983326111';
}

/**
 * Tabellenname für minimierte Sperrnotizen nach Austragung.
 */
function hp_get_newsletter_suppression_table_name(): string {
	global $wpdb;

	return $wpdb->prefix . 'hp_newsletter_suppressions';
}

/**
 * Tabellenname für lokale Newsletter-Abonnements.
 */
function hp_get_newsletter_table_name(): string {
	global $wpdb;

	return $wpdb->prefix . 'hp_newsletter_subscribers';
}

/**
 * Version der lokalen Newsletter-Struktur.
 */
function hp_get_newsletter_db_version(): string {
	return '1.1.0';
}

/**
 * Version des Einwilligungstexts.
 */
function hp_get_newsletter_consent_version(): string {
	return '2026-03-08';
}

/**
 * Gespeicherter Einwilligungstext.
 */
function hp_get_newsletter_consent_copy(): string {
	return 'Ich möchte E-Mails zu neuen Essays und ausgewählten Notizen erhalten. Abmeldung jederzeit über den Link in jeder Mail.';
}

/**
 * Normalisiert E-Mail-Adressen für Speicherung und Vergleich.
 */
function hp_normalize_newsletter_email( string $email ): string {
	return strtolower( trim( $email ) );
}

/**
 * Hash einer E-Mail-Adresse für minimierte Sperrnotizen.
 */
function hp_hash_newsletter_email( string $email ): string {
	$email = hp_normalize_newsletter_email( $email );

	return '' !== $email ? hash( 'sha256', $email ) : '';
}

/**
 * Maskierte Darstellung einer E-Mail-Adresse.
 */
function hp_mask_newsletter_email( string $email ): string {
	$email = hp_normalize_newsletter_email( $email );

	if ( '' === $email || false === strpos( $email, '@' ) ) {
		return '';
	}

	[ $local, $domain ] = explode( '@', $email, 2 );
	$local_length       = strlen( $local );

	if ( $local_length <= 2 ) {
		$masked_local = substr( $local, 0, 1 ) . '***';
	} else {
		$masked_local = substr( $local, 0, 2 ) . str_repeat( '*', max( 3, $local_length - 2 ) );
	}

	return $masked_local . '@' . $domain;
}

