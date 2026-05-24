<?php
/**
 * Newsletter database installation.
 *
 * @package Hasimuener_Journal
 */


defined( 'ABSPATH' ) || exit;

/**
 * Installiert oder aktualisiert die lokale Newsletter-Tabelle.
 */
function hp_maybe_install_newsletter_table(): void {
	$installed_version = (string) get_option( 'hp_newsletter_db_version', '' );

	if ( hp_get_newsletter_db_version() === $installed_version ) {
		return;
	}

	global $wpdb;

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	$table_name      = hp_get_newsletter_table_name();
	$suppression_table_name = hp_get_newsletter_suppression_table_name();
	$charset_collate = $wpdb->get_charset_collate();
	$sql             = "CREATE TABLE {$table_name} (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		email varchar(190) NOT NULL,
		status varchar(20) NOT NULL DEFAULT 'pending',
		source varchar(50) NOT NULL DEFAULT '',
		source_url varchar(255) NOT NULL DEFAULT '',
		consent_version varchar(20) NOT NULL DEFAULT '',
		consent_copy text NULL,
		ip_hash char(64) NOT NULL DEFAULT '',
		user_agent_hash char(64) NOT NULL DEFAULT '',
		confirm_token char(64) NOT NULL DEFAULT '',
		unsubscribe_token char(64) NOT NULL DEFAULT '',
		subscribed_at datetime NULL,
		confirmed_at datetime NULL,
		unsubscribed_at datetime NULL,
		confirm_sent_at datetime NULL,
		created_at datetime NOT NULL,
		updated_at datetime NOT NULL,
		PRIMARY KEY  (id),
		UNIQUE KEY email (email),
		KEY status (status),
		KEY confirm_token (confirm_token),
		KEY unsubscribe_token (unsubscribe_token)
	) {$charset_collate};

	CREATE TABLE {$suppression_table_name} (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		email_hash char(64) NOT NULL,
		email_mask varchar(190) NOT NULL DEFAULT '',
		source varchar(50) NOT NULL DEFAULT '',
		unsubscribed_at datetime NOT NULL,
		retain_until datetime NOT NULL,
		created_at datetime NOT NULL,
		updated_at datetime NOT NULL,
		PRIMARY KEY  (id),
		UNIQUE KEY email_hash (email_hash),
		KEY retain_until (retain_until)
	) {$charset_collate};";

	dbDelta( $sql );

	update_option( 'hp_newsletter_db_version', hp_get_newsletter_db_version(), false );
}
add_action( 'init', 'hp_maybe_install_newsletter_table', 26 );
