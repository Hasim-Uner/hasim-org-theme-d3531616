<?php
/**
 * Newsletter-Opt-in ohne Plugin.
 *
 * Native Anmeldung mit Double-Opt-in, lokaler Speicherung,
 * Export in der WordPress-Verwaltung und bewusst knapper UX.
 *
 * @package Hasimuener_Journal
 * @since   6.4.0
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

/**
 * Newsletter-Eintrag per E-Mail laden.
 *
 * @return array<string, string>|null
 */
function hp_get_newsletter_subscriber_by_email( string $email ): ?array {
	global $wpdb;

	$table_name = hp_get_newsletter_table_name();
	$email      = hp_normalize_newsletter_email( $email );

	if ( '' === $email ) {
		return null;
	}

	$row = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT * FROM {$table_name} WHERE email = %s LIMIT 1",
			$email
		),
		ARRAY_A
	);

	return is_array( $row ) ? array_map( 'strval', $row ) : null;
}

/**
 * Newsletter-Eintrag per ID laden.
 *
 * @return array<string, string>|null
 */
function hp_get_newsletter_subscriber_by_id( int $subscriber_id ): ?array {
	global $wpdb;

	if ( $subscriber_id <= 0 ) {
		return null;
	}

	$table_name = hp_get_newsletter_table_name();
	$row        = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT * FROM {$table_name} WHERE id = %d LIMIT 1",
			$subscriber_id
		),
		ARRAY_A
	);

	return is_array( $row ) ? array_map( 'strval', $row ) : null;
}

/**
 * Newsletter-Eintrag per Token laden.
 *
 * @return array<string, string>|null
 */
function hp_get_newsletter_subscriber_by_token( string $column, string $token ): ?array {
	if ( ! in_array( $column, [ 'confirm_token', 'unsubscribe_token' ], true ) ) {
		return null;
	}

	global $wpdb;

	$table_name = hp_get_newsletter_table_name();
	$token      = trim( $token );

	if ( '' === $token ) {
		return null;
	}

	$row = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT * FROM {$table_name} WHERE {$column} = %s LIMIT 1",
			$token
		),
		ARRAY_A
	);

	return is_array( $row ) ? array_map( 'strval', $row ) : null;
}

/**
 * Legt einen Pending-Eintrag an oder aktualisiert ihn.
 *
 * @return array<string, string>|WP_Error
 */
function hp_upsert_pending_newsletter_subscriber( string $email, string $source, string $source_url ) {
	global $wpdb;

	$table_name   = hp_get_newsletter_table_name();
	$now          = current_time( 'mysql' );
	$email        = hp_normalize_newsletter_email( $email );
	$source       = sanitize_key( $source );
	$source_url   = hp_get_newsletter_redirect_target( $source_url );
	$fingerprint  = hp_get_newsletter_request_fingerprint();
	$confirm_token = hp_generate_newsletter_token();
	$unsubscribe_token = hp_generate_newsletter_token();
	$email_hash   = hp_hash_newsletter_email( $email );
	$existing     = hp_get_newsletter_subscriber_by_email( $email );

	if ( '' !== $email_hash ) {
		$wpdb->delete(
			hp_get_newsletter_suppression_table_name(),
			[ 'email_hash' => $email_hash ],
			[ '%s' ]
		);
	}

	$data = [
		'email'             => $email,
		'status'            => 'pending',
		'source'            => $source,
		'source_url'        => $source_url,
		'consent_version'   => hp_get_newsletter_consent_version(),
		'consent_copy'      => hp_get_newsletter_consent_copy(),
		'ip_hash'           => $fingerprint['ip_hash'],
		'user_agent_hash'   => $fingerprint['user_agent_hash'],
		'confirm_token'     => $confirm_token,
		'unsubscribe_token' => $unsubscribe_token,
		'subscribed_at'     => $now,
		'confirm_sent_at'   => $now,
		'updated_at'        => $now,
	];

	if ( $existing ) {
		$updated = $wpdb->update(
			$table_name,
			$data,
			[ 'id' => (int) $existing['id'] ],
			[
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
			],
			[ '%d' ]
		);

		if ( false === $updated ) {
			return new WP_Error( 'newsletter_update_failed', 'newsletter_update_failed' );
		}

		if ( 'active' !== $existing['status'] ) {
			$wpdb->update(
				$table_name,
				[
					'confirmed_at'   => null,
					'unsubscribed_at'=> null,
				],
				[ 'id' => (int) $existing['id'] ],
				[ '%s', '%s' ],
				[ '%d' ]
			);
		}
	} else {
		$inserted = $wpdb->insert(
			$table_name,
			array_merge(
				$data,
				[
					'created_at' => $now,
				]
			),
			[
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
			]
		);

		if ( false === $inserted ) {
			return new WP_Error( 'newsletter_insert_failed', 'newsletter_insert_failed' );
		}
	}

	$subscriber = hp_get_newsletter_subscriber_by_email( $email );

	if ( ! $subscriber ) {
		return new WP_Error( 'newsletter_lookup_failed', 'newsletter_lookup_failed' );
	}

	return $subscriber;
}

/**
 * Aktualisiert den Status eines Eintrags.
 */
function hp_update_newsletter_subscriber_status( int $subscriber_id, string $status, array $extra = [] ): bool {
	global $wpdb;

	$table_name = hp_get_newsletter_table_name();
	$data       = array_merge(
		[
			'status'     => $status,
			'updated_at' => current_time( 'mysql' ),
		],
		$extra
	);

	$formats = [];

	foreach ( array_keys( $data ) as $key ) {
		$formats[] = in_array( $key, [ 'confirmed_at', 'unsubscribed_at', 'updated_at' ], true ) ? '%s' : '%s';
	}

	$result = $wpdb->update(
		$table_name,
		$data,
		[ 'id' => $subscriber_id ],
		$formats,
		[ '%d' ]
	);

	return false !== $result;
}

/**
 * Löscht einen Newsletter-Eintrag endgültig.
 */
function hp_delete_newsletter_subscriber( int $subscriber_id ): bool {
	global $wpdb;

	if ( $subscriber_id <= 0 ) {
		return false;
	}

	$deleted = $wpdb->delete(
		hp_get_newsletter_table_name(),
		[ 'id' => $subscriber_id ],
		[ '%d' ]
	);

	return false !== $deleted;
}

/**
 * Verschiebt eine Adresse in eine minimierte Sperrnotiz.
 *
 * @param array<string, string> $subscriber Datensatz.
 */
function hp_suppress_newsletter_subscriber( array $subscriber ): bool {
	global $wpdb;

	$email_hash = hp_hash_newsletter_email( (string) ( $subscriber['email'] ?? '' ) );

	if ( '' === $email_hash || empty( $subscriber['id'] ) ) {
		return false;
	}

	$now          = current_time( 'mysql' );
	$retain_until = gmdate( 'Y-m-d H:i:s', time() + ( DAY_IN_SECONDS * hp_get_newsletter_suppression_retention_days() ) );
	$inserted     = $wpdb->replace(
		hp_get_newsletter_suppression_table_name(),
		[
			'email_hash'      => $email_hash,
			'email_mask'      => hp_mask_newsletter_email( (string) $subscriber['email'] ),
			'source'          => (string) ( $subscriber['source'] ?? '' ),
			'unsubscribed_at' => $now,
			'retain_until'    => $retain_until,
			'created_at'      => $now,
			'updated_at'      => $now,
		],
		[
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
		]
	);

	if ( false === $inserted ) {
		return false;
	}

	return hp_delete_newsletter_subscriber( (int) $subscriber['id'] );
}

/**
 * Zählt minimierte Sperrnotizen.
 */
function hp_get_newsletter_suppression_count(): int {
	global $wpdb;

	return (int) $wpdb->get_var( "SELECT COUNT(*) FROM " . hp_get_newsletter_suppression_table_name() );
}

/**
 * Lädt minimierte Sperrnotizen für die Verwaltung.
 *
 * @return array<int, array<string, string>>
 */
function hp_get_recent_newsletter_suppressions( int $limit = 50 ): array {
	global $wpdb;

	$limit = max( 1, min( 100, $limit ) );
	$rows  = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT id, email_mask, source, unsubscribed_at
			FROM " . hp_get_newsletter_suppression_table_name() . "
			ORDER BY unsubscribed_at DESC
			LIMIT %d",
			$limit
		),
		ARRAY_A
	);

	if ( ! is_array( $rows ) ) {
		return [];
	}

	return array_map(
		static function ( array $row ): array {
			return array_map( 'strval', $row );
		},
		$rows
	);
}

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

/**
 * Baut eine HTML-Mailhülle für Newsletter-Mails.
 */
function hp_get_newsletter_mail_shell( string $title, string $intro_html, string $body_html, string $footnote_html ): string {
	$site_name     = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
	$contact_email = hp_get_newsletter_contact_email();
	$contact_url   = 'mailto:' . $contact_email;
	$imprint_url   = home_url( '/impressum/' );
	$privacy_url   = home_url( '/datenschutz/' );

	return '<!doctype html>
<html lang="de">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>' . esc_html( $title ) . '</title>
</head>
<body style="margin:0;padding:0;background:#f4f3ef;color:#1b1b1b;">
	<table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="background:#f4f3ef;margin:0;padding:24px 0;">
		<tr>
			<td align="center">
				<table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="max-width:680px;background:#ffffff;border:1px solid rgba(17,17,17,0.08);border-radius:22px;overflow:hidden;">
					<tr>
						<td style="padding:30px 34px 16px;border-top:4px solid #b12a2a;">
							<p style="margin:0 0 10px;font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:1.5;letter-spacing:1.8px;text-transform:uppercase;color:#696969;">' . esc_html( $site_name ) . '</p>
							<h1 style="margin:0;font-family:Georgia,Times New Roman,serif;font-size:30px;line-height:1.2;color:#111111;font-weight:700;">' . esc_html( $title ) . '</h1>
						</td>
					</tr>
					<tr>
						<td style="padding:0 34px 0;">' . $intro_html . '</td>
					</tr>
					<tr>
						<td style="padding:4px 34px 10px;">' . $body_html . '</td>
					</tr>
					<tr>
						<td style="padding:10px 34px 30px;">
							' . $footnote_html . '
							<table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="border-top:1px solid #ece7df;padding-top:14px;margin-top:16px;">
								<tr>
									<td style="padding-top:14px;">
										<p style="margin:0 0 6px;font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:1.6;color:#696969;">Haşim Üner</p>
										<p style="margin:0 0 6px;font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:1.6;color:#696969;"><a href="' . esc_url( $contact_url ) . '" style="color:#b12a2a;text-decoration:none;">' . esc_html( $contact_email ) . '</a></p>
										<p style="margin:0 0 6px;font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:1.6;color:#696969;"><a href="' . esc_url( home_url( '/' ) ) . '" style="color:#b12a2a;text-decoration:none;">hasimuener.org</a></p>
										<p style="margin:10px 0 0;font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:1.6;color:#696969;"><a href="' . esc_url( $imprint_url ) . '" style="color:#b12a2a;text-decoration:none;">Impressum</a> · <a href="' . esc_url( $privacy_url ) . '" style="color:#b12a2a;text-decoration:none;">Datenschutz</a></p>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</body>
</html>';
}

/**
 * Betreff der DOI-Mail.
 */
function hp_get_newsletter_confirmation_subject(): string {
	return 'Bitte bestätigen Sie Ihre Anmeldung bei hasimuener.org';
}

/**
 * Betreff der Willkommensmail.
 */
function hp_get_newsletter_welcome_subject(): string {
	return 'Ihre Anmeldung für neue Texte ist bestätigt';
}

/**
 * HTML der DOI-Mail.
 *
 * @param array<string, string> $subscriber Datensatz.
 */
function hp_get_newsletter_confirmation_html( array $subscriber ): string {
	$confirm_url = hp_get_newsletter_confirm_url( $subscriber['confirm_token'] );
	$intro_html  = '<p style="margin:0 0 16px;font-family:Georgia,Times New Roman,serif;font-size:17px;line-height:1.75;color:#333333;">Sie haben sich für <strong>' . esc_html( hp_get_newsletter_label() ) . '</strong> eingetragen. Bitte bestätigen Sie die Anmeldung mit einem Klick.</p>';
	$body_html   = '<table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="border:1px solid #e6e1d8;border-radius:16px;background:#faf8f5;">
		<tr>
			<td style="padding:18px 20px;">
				<p style="margin:0 0 12px;font-family:Georgia,Times New Roman,serif;font-size:16px;line-height:1.7;color:#333333;">Sie erhalten danach kurze Hinweise auf neue Essays und ausgewählte Notizen. Keine Werbung. Keine täglichen Strecken. Nur dann, wenn ein neuer Text wirklich erschienen ist.</p>
				<p style="margin:0 0 16px;font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:1.6;letter-spacing:1.4px;text-transform:uppercase;color:#696969;">Double-Opt-in erforderlich</p>
				<p style="margin:0;"><a href="' . esc_url( $confirm_url ) . '" style="display:inline-block;padding:14px 22px;border-radius:999px;background:#111111;color:#ffffff;font-family:Arial,Helvetica,sans-serif;font-size:13px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;text-decoration:none;">Anmeldung bestätigen</a></p>
			</td>
		</tr>
	</table>';
	$footnote_html = '<p style="margin:0;font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:1.7;color:#696969;">Wenn Sie sich nicht selbst eingetragen haben, ignorieren Sie diese E-Mail einfach. Ohne Bestätigung erfolgt keine Anmeldung.</p>';

	return hp_get_newsletter_mail_shell(
		hp_get_newsletter_confirmation_subject(),
		$intro_html,
		$body_html,
		$footnote_html
	);
}

/**
 * Textversion der DOI-Mail.
 *
 * @param array<string, string> $subscriber Datensatz.
 */
function hp_get_newsletter_confirmation_text( array $subscriber ): string {
	return implode(
		"\n\n",
		[
			hp_get_newsletter_confirmation_subject(),
			'Sie haben sich für "' . hp_get_newsletter_label() . '" eingetragen.',
			'Bitte bestätigen Sie die Anmeldung über diesen Link:',
			hp_get_newsletter_confirm_url( $subscriber['confirm_token'] ),
			'Sie erhalten danach kurze Hinweise auf neue Essays und ausgewählte Notizen. Keine Werbung. Keine täglichen Strecken.',
			'Wenn Sie sich nicht selbst eingetragen haben, ignorieren Sie diese E-Mail einfach. Ohne Bestätigung erfolgt keine Anmeldung.',
		]
	);
}

/**
 * HTML der Willkommensmail.
 *
 * @param array<string, string> $subscriber Datensatz.
 */
function hp_get_newsletter_welcome_html( array $subscriber ): string {
	$archive_url      = get_post_type_archive_link( 'essay' ) ?: home_url( '/' );
	$unsubscribe_url  = hp_get_newsletter_unsubscribe_url( $subscriber['unsubscribe_token'] );
	$x_url            = hp_get_newsletter_x_url();
	$intro_html       = '<p style="margin:0 0 16px;font-family:Georgia,Times New Roman,serif;font-size:17px;line-height:1.75;color:#333333;">Ihre Anmeldung ist bestätigt. Künftig erhalten Sie eine kurze Nachricht, wenn ein neuer Essay erscheint oder eine Notiz den laufenden Gedanken sinnvoll vertieft.</p>';
	$body_html        = '<table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="border:1px solid #e6e1d8;border-radius:16px;background:#faf8f5;">
		<tr>
			<td style="padding:18px 20px;">
				<p style="margin:0 0 10px;font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:1.6;letter-spacing:1.4px;text-transform:uppercase;color:#696969;">Was Sie erwarten können</p>
				<ul style="margin:0;padding-left:20px;font-family:Georgia,Times New Roman,serif;font-size:16px;line-height:1.8;color:#333333;">
					<li>neue Essays direkt nach Veröffentlichung</li>
					<li>ausgewählte Notizen nur dann, wenn sie den Gedanken erweitern</li>
					<li>keine Werbung, kein Tracking in den E-Mails</li>
				</ul>
				<p style="margin:16px 0 0;"><a href="' . esc_url( $archive_url ) . '" style="display:inline-block;padding:14px 22px;border-radius:999px;background:#111111;color:#ffffff;font-family:Arial,Helvetica,sans-serif;font-size:13px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;text-decoration:none;">Zu den Essays</a></p>
			</td>
		</tr>
	</table>
	<p style="margin:16px 0 0;font-family:Georgia,Times New Roman,serif;font-size:16px;line-height:1.7;color:#333333;">Für kürzere Hinweise und laufende Gedanken können Sie mir auch auf <a href="' . esc_url( $x_url ) . '" style="color:#b12a2a;text-decoration:none;">X folgen</a>.</p>';
	$footnote_html    = '<p style="margin:0;font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:1.7;color:#696969;">Wenn Sie diese Hinweise nicht mehr erhalten möchten, können Sie sich jederzeit mit einem Klick wieder abmelden: <a href="' . esc_url( $unsubscribe_url ) . '" style="color:#b12a2a;text-decoration:none;">Newsletter abbestellen</a>.</p>';

	return hp_get_newsletter_mail_shell(
		hp_get_newsletter_welcome_subject(),
		$intro_html,
		$body_html,
		$footnote_html
	);
}

/**
 * Textversion der Willkommensmail.
 *
 * @param array<string, string> $subscriber Datensatz.
 */
function hp_get_newsletter_welcome_text( array $subscriber ): string {
	$archive_url     = get_post_type_archive_link( 'essay' ) ?: home_url( '/' );
	$unsubscribe_url = hp_get_newsletter_unsubscribe_url( $subscriber['unsubscribe_token'] );

	return implode(
		"\n\n",
		[
			hp_get_newsletter_welcome_subject(),
			'Ihre Anmeldung ist bestätigt.',
			'Sie erhalten künftig kurze Hinweise auf neue Essays und ausgewählte Notizen.',
			'Was Sie erwarten können:',
			'- neue Essays direkt nach Veröffentlichung' . "\n" . '- ausgewählte Notizen nur dann, wenn sie den Gedanken erweitern' . "\n" . '- keine Werbung, kein Tracking in den E-Mails',
			'Zu den Essays: ' . $archive_url,
			'Abmelden: ' . $unsubscribe_url,
			'Für kurze Hinweise: ' . hp_get_newsletter_x_url(),
		]
	);
}

/**
 * Betreff der Austragungsbestätigung.
 */
function hp_get_newsletter_unsubscribed_subject(): string {
	return 'Ihre Adresse wurde aus dem Verteiler ausgetragen';
}

/**
 * HTML der Austragungsbestätigung.
 *
 * @param array<string, string> $subscriber Datensatz.
 */
function hp_get_newsletter_unsubscribed_html( array $subscriber ): string {
	$resubscribe_url = hp_get_newsletter_anchor_url();
	$intro_html      = '<p style="margin:0 0 16px;font-family:Georgia,Times New Roman,serif;font-size:17px;line-height:1.75;color:#333333;">Die Adresse <strong>' . esc_html( $subscriber['email'] ) . '</strong> wurde aus dem Verteiler für neue Texte ausgetragen.</p>';
	$body_html       = '<table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="border:1px solid #e6e1d8;border-radius:16px;background:#faf8f5;">
		<tr>
			<td style="padding:18px 20px;">
				<p style="margin:0 0 12px;font-family:Georgia,Times New Roman,serif;font-size:16px;line-height:1.7;color:#333333;">Sie erhalten an diese Adresse keine weiteren Hinweise auf neue Essays oder Notizen mehr.</p>
				<p style="margin:0;font-family:Georgia,Times New Roman,serif;font-size:16px;line-height:1.7;color:#333333;">Wenn das ein Irrtum war, können Sie sich jederzeit erneut eintragen: <a href="' . esc_url( $resubscribe_url ) . '" style="color:#b12a2a;text-decoration:none;">Zur Anmeldung</a>.</p>
			</td>
		</tr>
	</table>';
	$footnote_html   = '<p style="margin:0;font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:1.7;color:#696969;">Wenn Sie diese Austragung nicht veranlasst haben, antworten Sie bitte direkt auf diese E-Mail.</p>';

	return hp_get_newsletter_mail_shell(
		hp_get_newsletter_unsubscribed_subject(),
		$intro_html,
		$body_html,
		$footnote_html
	);
}

/**
 * Textversion der Austragungsbestätigung.
 *
 * @param array<string, string> $subscriber Datensatz.
 */
function hp_get_newsletter_unsubscribed_text( array $subscriber ): string {
	return implode(
		"\n\n",
		[
			hp_get_newsletter_unsubscribed_subject(),
			'Die Adresse ' . $subscriber['email'] . ' wurde aus dem Verteiler für neue Texte ausgetragen.',
			'Sie erhalten an diese Adresse keine weiteren Hinweise mehr.',
			'Neu anmelden: ' . hp_get_newsletter_anchor_url(),
			'Wenn Sie diese Austragung nicht veranlasst haben, antworten Sie bitte direkt auf diese E-Mail.',
		]
	);
}

/**
 * Generischer Versand für Newsletter-Mails.
 */
function hp_send_newsletter_mail( string $to_email, string $subject, string $html_content, string $text_content, array $tags = [] ): bool {
	$contact_email = hp_get_newsletter_contact_email();
	$from_header   = 'From: ' . hp_get_newsletter_sender_name() . ' <' . hp_get_newsletter_sender_email() . '>';
	$reply_header  = 'Reply-To: ' . hp_get_newsletter_sender_name() . ' <' . $contact_email . '>';
	$headers       = [
		'Content-Type: text/html; charset=UTF-8',
		$from_header,
		$reply_header,
	];

	if ( function_exists( 'hp_has_brevo_smtp_config' ) && hp_has_brevo_smtp_config() && function_exists( 'hp_send_wp_mail_via_brevo_smtp' ) ) {
		$mail_sent = hp_send_wp_mail_via_brevo_smtp(
			$to_email,
			$subject,
			$html_content,
			$headers,
			$text_content
		);

		if ( $mail_sent ) {
			return true;
		}
	}

	if ( function_exists( 'hp_has_brevo_api_key' ) && hp_has_brevo_api_key() && function_exists( 'hp_send_brevo_transactional_email' ) ) {
		$response = hp_send_brevo_transactional_email(
			[
				'to_email'       => $to_email,
				'subject'        => $subject,
				'html_content'   => $html_content,
				'text_content'   => $text_content,
				'reply_to_email' => $contact_email,
				'reply_to_name'  => hp_get_newsletter_sender_name(),
				'tags'           => $tags,
			]
		);

		if ( ! empty( $response['success'] ) ) {
			return true;
		}
	}

	$alt_body_setter = static function ( PHPMailer\PHPMailer\PHPMailer $phpmailer ) use ( $text_content ): void {
		$phpmailer->AltBody = $text_content;
	};

	add_action( 'phpmailer_init', $alt_body_setter );
	$mail_sent = wp_mail( $to_email, $subject, $html_content, $headers );
	remove_action( 'phpmailer_init', $alt_body_setter );

	return $mail_sent;
}

/**
 * DOI-Mail versenden.
 *
 * @param array<string, string> $subscriber Datensatz.
 */
function hp_send_newsletter_confirmation_request( array $subscriber ): bool {
	return hp_send_newsletter_mail(
		$subscriber['email'],
		hp_get_newsletter_confirmation_subject(),
		hp_get_newsletter_confirmation_html( $subscriber ),
		hp_get_newsletter_confirmation_text( $subscriber ),
		[ 'newsletter', 'newsletter-confirmation' ]
	);
}

/**
 * Willkommensmail versenden.
 *
 * @param array<string, string> $subscriber Datensatz.
 */
function hp_send_newsletter_welcome_mail( array $subscriber ): bool {
	return hp_send_newsletter_mail(
		$subscriber['email'],
		hp_get_newsletter_welcome_subject(),
		hp_get_newsletter_welcome_html( $subscriber ),
		hp_get_newsletter_welcome_text( $subscriber ),
		[ 'newsletter', 'newsletter-welcome' ]
	);
}

/**
 * Austragungsbestätigung versenden.
 *
 * @param array<string, string> $subscriber Datensatz.
 */
function hp_send_newsletter_unsubscribed_mail( array $subscriber ): bool {
	return hp_send_newsletter_mail(
		$subscriber['email'],
		hp_get_newsletter_unsubscribed_subject(),
		hp_get_newsletter_unsubscribed_html( $subscriber ),
		hp_get_newsletter_unsubscribed_text( $subscriber ),
		[ 'newsletter', 'newsletter-unsubscribed' ]
	);
}

/**
 * Verarbeitet Newsletter-Anmeldungen.
 */
function hp_handle_newsletter_form_submission(): void {
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ?? 'GET' ) ) {
		wp_safe_redirect( home_url( '/' ) );
		exit;
	}

	$email      = isset( $_POST['hp_newsletter_email'] ) ? sanitize_email( (string) wp_unslash( $_POST['hp_newsletter_email'] ) ) : '';
	$source     = isset( $_POST['hp_newsletter_source'] ) ? sanitize_key( (string) wp_unslash( $_POST['hp_newsletter_source'] ) ) : 'site';
	$target_url = isset( $_POST['hp_newsletter_redirect'] ) ? hp_get_newsletter_redirect_target( (string) wp_unslash( $_POST['hp_newsletter_redirect'] ) ) : home_url( '/' );
	$consent    = isset( $_POST['hp_newsletter_consent'] ) ? (string) wp_unslash( $_POST['hp_newsletter_consent'] ) : '';
	$flash      = [
		'status' => 'error',
		'source' => $source,
		'fields' => [
			'email' => $email,
		],
	];

	$nonce = isset( $_POST['hp_newsletter_nonce'] ) ? (string) wp_unslash( $_POST['hp_newsletter_nonce'] ) : '';

	if ( ! wp_verify_nonce( $nonce, 'hp_newsletter_submit' ) ) {
		$flash['message'] = 'Das Formular ist nicht mehr gültig. Bitte laden Sie die Seite neu.';
		hp_redirect_newsletter( $target_url, $flash );
	}

	$honeypot = isset( $_POST['hp_newsletter_website'] ) ? trim( (string) wp_unslash( $_POST['hp_newsletter_website'] ) ) : '';

	if ( '' !== $honeypot ) {
		$flash['message'] = 'Die Anmeldung konnte nicht verarbeitet werden. Bitte versuchen Sie es erneut.';
		hp_redirect_newsletter( $target_url, $flash );
	}

	$settings    = hp_get_newsletter_form_settings();
	$rendered_at = isset( $_POST['hp_newsletter_rendered_at'] ) ? (int) wp_unslash( $_POST['hp_newsletter_rendered_at'] ) : 0;
	$token       = isset( $_POST['hp_newsletter_render_token'] ) ? (string) wp_unslash( $_POST['hp_newsletter_render_token'] ) : '';

	if ( $rendered_at <= 0 || '' === $token || ! hash_equals( hp_get_newsletter_form_render_token( $rendered_at ), $token ) ) {
		$flash['message'] = 'Das Formular ist abgelaufen. Bitte laden Sie die Seite neu.';
		hp_redirect_newsletter( $target_url, $flash );
	}

	$elapsed = time() - $rendered_at;

	if ( $elapsed < $settings['min_seconds'] ) {
		$flash['message'] = 'Bitte nehmen Sie sich einen kurzen Moment Zeit und senden Sie das Formular dann erneut.';
		hp_redirect_newsletter( $target_url, $flash );
	}

	if ( $elapsed > $settings['max_age'] ) {
		$flash['message'] = 'Das Formular ist abgelaufen. Bitte laden Sie die Seite neu.';
		hp_redirect_newsletter( $target_url, $flash );
	}

	if ( '' === $email || ! is_email( $email ) ) {
		$flash['message'] = 'Bitte geben Sie eine gültige E-Mail-Adresse an.';
		hp_redirect_newsletter( $target_url, $flash );
	}

	if ( '1' !== $consent ) {
		$flash['message'] = 'Bitte bestätigen Sie, dass Sie Hinweise auf neue Texte per E-Mail erhalten möchten.';
		hp_redirect_newsletter( $target_url, $flash );
	}

	$rate_key     = hp_get_newsletter_rate_key( $email );
	$last_sent_at = (int) get_transient( $rate_key );

	if ( $last_sent_at > 0 && ( time() - $last_sent_at ) < $settings['rate_window'] ) {
		$flash['message'] = 'Bitte warten Sie einen kurzen Moment, bevor Sie dieselbe Adresse erneut eintragen.';
		hp_redirect_newsletter( $target_url, $flash );
	}

	$existing = hp_get_newsletter_subscriber_by_email( $email );

	if ( $existing && 'active' === $existing['status'] ) {
		hp_redirect_newsletter(
			$target_url,
			[
				'status'  => 'success',
				'source'  => $source,
				'message' => 'Diese Adresse ist bereits eingetragen. Neue Texte gehen künftig an dieses Postfach.',
			]
		);
	}

	$subscriber = hp_upsert_pending_newsletter_subscriber( $email, $source, $target_url );

	if ( is_wp_error( $subscriber ) ) {
		$flash['message'] = 'Die Anmeldung konnte technisch nicht abgeschlossen werden. Bitte schreiben Sie alternativ an ' . hp_get_newsletter_contact_email() . '.';
		hp_redirect_newsletter( $target_url, $flash );
	}

	$mail_sent = hp_send_newsletter_confirmation_request( $subscriber );

	if ( ! $mail_sent ) {
		$flash['message'] = 'Die Bestätigungs-E-Mail konnte im Moment nicht versendet werden. Bitte versuchen Sie es später erneut.';
		hp_redirect_newsletter( $target_url, $flash );
	}

	set_transient( $rate_key, time(), $settings['rate_window'] );

	hp_redirect_newsletter(
		$target_url,
		[
			'status'  => 'success',
			'source'  => $source,
			'message' => 'Fast geschafft. Bitte bestätigen Sie Ihre Anmeldung über die E-Mail, die gerade unterwegs ist.',
		]
	);
}
add_action( 'admin_post_nopriv_hp_subscribe_newsletter', 'hp_handle_newsletter_form_submission' );
add_action( 'admin_post_hp_subscribe_newsletter', 'hp_handle_newsletter_form_submission' );

/**
 * Verarbeitet DOI-Bestätigungen.
 */
function hp_handle_newsletter_confirmation(): void {
	$token      = isset( $_GET['token'] ) ? sanitize_text_field( (string) wp_unslash( $_GET['token'] ) ) : '';
	$subscriber = hp_get_newsletter_subscriber_by_token( 'confirm_token', $token );
	$target_url = $subscriber ? hp_get_newsletter_redirect_target( $subscriber['source_url'] ) : home_url( '/' );

	if ( ! $subscriber ) {
		hp_redirect_newsletter(
			home_url( '/' ),
			[
				'status'  => 'error',
				'message' => 'Der Bestätigungslink ist nicht mehr gültig. Bitte tragen Sie sich bei Bedarf erneut ein.',
			]
		);
	}

	if ( 'active' === $subscriber['status'] ) {
		hp_redirect_newsletter(
			$target_url,
			[
				'status'  => 'success',
				'message' => 'Ihre Anmeldung war bereits bestätigt.',
			]
		);
	}

	if ( 'pending' !== $subscriber['status'] ) {
		hp_redirect_newsletter(
			$target_url,
			[
				'status'  => 'error',
				'message' => 'Dieser Bestätigungslink kann nicht mehr verwendet werden. Bitte tragen Sie sich erneut ein.',
			]
		);
	}

	$updated = hp_update_newsletter_subscriber_status(
		(int) $subscriber['id'],
		'active',
		[
			'confirmed_at' => current_time( 'mysql' ),
		]
	);

	if ( ! $updated ) {
		hp_redirect_newsletter(
			$target_url,
			[
				'status'  => 'error',
				'message' => 'Die Anmeldung konnte technisch nicht bestätigt werden. Bitte versuchen Sie es erneut.',
			]
		);
	}

	$active_subscriber = hp_get_newsletter_subscriber_by_email( $subscriber['email'] );

	if ( $active_subscriber ) {
		hp_send_newsletter_welcome_mail( $active_subscriber );
	}

	hp_redirect_newsletter(
		$target_url,
		[
			'status'  => 'success',
			'message' => 'Ihre Anmeldung ist bestätigt. Künftige Hinweise auf neue Texte gehen an dieses Postfach.',
		]
	);
}
add_action( 'admin_post_nopriv_hp_confirm_newsletter', 'hp_handle_newsletter_confirmation' );
add_action( 'admin_post_hp_confirm_newsletter', 'hp_handle_newsletter_confirmation' );

/**
 * Verarbeitet Abmeldungen.
 */
function hp_handle_newsletter_unsubscribe(): void {
	$token      = isset( $_GET['token'] ) ? sanitize_text_field( (string) wp_unslash( $_GET['token'] ) ) : '';
	$subscriber = hp_get_newsletter_subscriber_by_token( 'unsubscribe_token', $token );
	$target_url = $subscriber ? hp_get_newsletter_redirect_target( $subscriber['source_url'] ) : home_url( '/' );

	if ( ! $subscriber ) {
		hp_redirect_newsletter(
			home_url( '/' ),
			[
				'status'  => 'error',
				'message' => 'Der Abmeldelink ist nicht mehr gültig.',
			]
		);
	}

	$unsubscribed = hp_suppress_newsletter_subscriber( $subscriber );

	if ( ! $unsubscribed ) {
		hp_redirect_newsletter(
			$target_url,
			[
				'status'  => 'error',
				'message' => 'Die Abmeldung konnte technisch nicht verarbeitet werden.',
			]
		);
	}

	hp_send_newsletter_unsubscribed_mail( $subscriber );

	hp_redirect_newsletter(
		$target_url,
		[
			'status'  => 'success',
			'message' => 'Die Adresse wurde abgemeldet. Sie erhalten keine Hinweise auf neue Texte mehr.',
		]
	);
}
add_action( 'admin_post_nopriv_hp_unsubscribe_newsletter', 'hp_handle_newsletter_unsubscribe' );
add_action( 'admin_post_hp_unsubscribe_newsletter', 'hp_handle_newsletter_unsubscribe' );

/**
 * Verarbeitet manuelle Austragungen im Admin.
 */
function hp_handle_newsletter_admin_unsubscribe(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Sie haben nicht die erforderlichen Rechte.', 'hasimuener-journal' ) );
	}

	$subscriber_id = isset( $_GET['subscriber'] ) ? absint( $_GET['subscriber'] ) : 0;
	$redirect_url  = admin_url( 'admin.php?page=hp-newsletter' );

	if ( $subscriber_id <= 0 ) {
		wp_safe_redirect( add_query_arg( 'notice', 'invalid', $redirect_url ) );
		exit;
	}

	check_admin_referer( 'hp_newsletter_admin_unsubscribe_' . $subscriber_id );

	$subscriber = hp_get_newsletter_subscriber_by_id( $subscriber_id );

	if ( ! $subscriber ) {
		wp_safe_redirect( add_query_arg( 'notice', 'missing', $redirect_url ) );
		exit;
	}

	if ( hp_suppress_newsletter_subscriber( $subscriber ) ) {
		hp_send_newsletter_unsubscribed_mail( $subscriber );
	}

	wp_safe_redirect( add_query_arg( 'notice', 'manual_unsubscribed', $redirect_url ) );
	exit;
}
add_action( 'admin_post_hp_admin_unsubscribe_newsletter', 'hp_handle_newsletter_admin_unsubscribe' );

/**
 * Lädt Newsletter-Einträge für die Verwaltung.
 *
 * @return array<int, array<string, string>>
 */
function hp_get_recent_newsletter_subscribers( int $limit = 50, string $search = '', string $status = 'all' ): array {
	global $wpdb;

	if ( 'unsubscribed' === $status ) {
		return [];
	}

	$table_name = hp_get_newsletter_table_name();
	$limit      = max( 1, min( 100, $limit ) );
	$status     = in_array( $status, [ 'all', 'active', 'pending', 'unsubscribed' ], true ) ? $status : 'all';
	$search     = trim( $search );
	$sql        = "SELECT id, email, status, source, source_url, subscribed_at, confirmed_at, unsubscribed_at
		FROM {$table_name}
		WHERE 1=1";
	$params     = [];

	if ( 'all' !== $status ) {
		$sql      .= ' AND status = %s';
		$params[] = $status;
	}

	if ( '' !== $search ) {
		$like      = '%' . $wpdb->esc_like( $search ) . '%';
		$sql      .= ' AND (email LIKE %s OR source LIKE %s)';
		$params[]  = $like;
		$params[]  = $like;
	}

	$sql      .= ' ORDER BY updated_at DESC LIMIT %d';
	$params[]  = $limit;
	$query     = $wpdb->prepare( $sql, ...$params );
	$rows      = $wpdb->get_results( $query, ARRAY_A );

	if ( ! is_array( $rows ) ) {
		return [];
	}

	return array_map(
		static function ( array $row ): array {
			return array_map( 'strval', $row );
		},
		$rows
	);
}

/**
 * Löscht unbestätigte Newsletter-Anmeldungen nach Ablauf der Frist.
 */
function hp_cleanup_newsletter_pending_subscribers(): void {
	global $wpdb;

	$cutoff = gmdate( 'Y-m-d H:i:s', time() - ( DAY_IN_SECONDS * hp_get_newsletter_pending_retention_days() ) );

	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM " . hp_get_newsletter_table_name() . "
			WHERE status = %s
			AND confirm_sent_at < %s",
			'pending',
			$cutoff
		)
	);
}

/**
 * Löscht abgelaufene minimierte Sperrnotizen.
 */
function hp_cleanup_newsletter_suppressions(): void {
	global $wpdb;

	$cutoff = current_time( 'mysql' );

	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM " . hp_get_newsletter_suppression_table_name() . "
			WHERE retain_until < %s",
			$cutoff
		)
	);
}

/**
 * Rendert das Newsletter-Formular.
 *
 * @param array<string, mixed> $args Anzeigeparameter.
 */
function hp_render_newsletter_form( array $args = [] ): void {
	$defaults = [
		'id'           => 'newsletter-signup',
		'context'      => 'site',
		'variant'      => 'home',
		'eyebrow'      => hp_get_newsletter_label(),
		'title'        => 'Neue Texte per E-Mail.',
		'lede'         => 'Eine kurze Mail, wenn ein neuer Essay oder eine relevante Notiz erscheint.',
		'promises'     => [],
		'submit_label' => 'Anmelden',
		'show_x_link'  => false,
		'class_name'   => '',
		'return_url'   => hp_get_newsletter_current_url(),
	];

	$args               = wp_parse_args( $args, $defaults );
	$flash              = hp_get_newsletter_flash();
	$status             = isset( $flash['status'] ) ? (string) $flash['status'] : '';
	$message            = isset( $flash['message'] ) ? (string) $flash['message'] : '';
	$fields             = isset( $flash['fields'] ) && is_array( $flash['fields'] ) ? $flash['fields'] : [];
	$email_value        = isset( $fields['email'] ) ? (string) $fields['email'] : '';
	$privacy_url        = get_privacy_policy_url();
	$rendered_at        = time();
	$render_token       = hp_get_newsletter_form_render_token( $rendered_at );
	$section_classes    = trim( 'hp-newsletter hp-newsletter--' . sanitize_html_class( (string) $args['variant'] ) . ' ' . (string) $args['class_name'] );
	$return_url         = hp_get_newsletter_redirect_target( (string) $args['return_url'] );
	$promises           = is_array( $args['promises'] ) ? $args['promises'] : [];
	$x_url              = hp_get_newsletter_x_url();
	$form_id            = (string) $args['id'];
	$form_context       = sanitize_key( (string) $args['context'] );
	$consent_copy       = hp_get_newsletter_consent_copy();
?>
	<section id="<?php echo esc_attr( $form_id ); ?>" class="<?php echo esc_attr( $section_classes ); ?>" aria-labelledby="<?php echo esc_attr( $form_id . '-title' ); ?>">
	<?php if ( '' !== $message ) : ?>
		<script>
		(function() {
			var id = <?php echo wp_json_encode( $form_id ); ?>;
			function scrollToForm() {
				var el = document.getElementById( id );
				if ( el ) { el.scrollIntoView( { behavior: 'smooth', block: 'start' } ); }
			}
			if ( document.readyState === 'loading' ) {
				document.addEventListener( 'DOMContentLoaded', scrollToForm );
			} else {
				scrollToForm();
			}
		})();
		</script>
	<?php endif; ?>
		<div class="hp-newsletter__shell">
			<div class="hp-newsletter__intro">
				<p class="hp-newsletter__eyebrow"><?php echo esc_html( (string) $args['eyebrow'] ); ?></p>
				<h2 id="<?php echo esc_attr( $form_id . '-title' ); ?>" class="hp-newsletter__title"><?php echo esc_html( (string) $args['title'] ); ?></h2>
				<p class="hp-newsletter__lede"><?php echo esc_html( (string) $args['lede'] ); ?></p>

				<?php if ( $promises ) : ?>
					<ul class="hp-newsletter__promises" aria-label="Was Sie erhalten">
						<?php foreach ( $promises as $promise ) : ?>
							<li><?php echo esc_html( (string) $promise ); ?></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>

			<div class="hp-newsletter__form-wrap">
				<?php if ( '' !== $message ) : ?>
					<div class="hp-newsletter__notice hp-newsletter__notice--<?php echo 'success' === $status ? 'success' : 'error'; ?>" aria-live="polite">
						<p><?php echo esc_html( $message ); ?></p>
					</div>
				<?php endif; ?>

				<form class="hp-newsletter__form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
					<input type="hidden" name="action" value="hp_subscribe_newsletter">
					<input type="hidden" name="hp_newsletter_source" value="<?php echo esc_attr( $form_context ); ?>">
					<input type="hidden" name="hp_newsletter_redirect" value="<?php echo esc_attr( $return_url ); ?>">
					<input type="hidden" name="hp_newsletter_rendered_at" value="<?php echo esc_attr( (string) $rendered_at ); ?>">
					<input type="hidden" name="hp_newsletter_render_token" value="<?php echo esc_attr( $render_token ); ?>">
					<?php wp_nonce_field( 'hp_newsletter_submit', 'hp_newsletter_nonce' ); ?>

					<div class="hp-newsletter__honeypot" aria-hidden="true">
						<label for="<?php echo esc_attr( $form_id . '-website' ); ?>">Website</label>
						<input id="<?php echo esc_attr( $form_id . '-website' ); ?>" type="text" name="hp_newsletter_website" value="" tabindex="-1" autocomplete="off">
					</div>

					<div class="hp-newsletter__primary">
						<p class="hp-newsletter__field">
							<label for="<?php echo esc_attr( $form_id . '-email' ); ?>">E-Mail-Adresse</label>
							<input id="<?php echo esc_attr( $form_id . '-email' ); ?>" name="hp_newsletter_email" type="email" maxlength="190" autocomplete="email" value="<?php echo esc_attr( $email_value ); ?>" placeholder="name@beispiel.de" required>
						</p>

						<button class="hp-newsletter__submit" type="submit"><?php echo esc_html( (string) $args['submit_label'] ); ?></button>
					</div>

					<label class="hp-newsletter__consent" for="<?php echo esc_attr( $form_id . '-consent' ); ?>">
						<input id="<?php echo esc_attr( $form_id . '-consent' ); ?>" name="hp_newsletter_consent" type="checkbox" value="1" required>
						<span><?php echo esc_html( $consent_copy ); ?><?php if ( $privacy_url ) : ?> Mehr in der <a href="<?php echo esc_url( $privacy_url ); ?>">Datenschutzerklärung</a>.<?php endif; ?></span>
					</label>

					<?php if ( ! empty( $args['show_x_link'] ) ) : ?>
						<div class="hp-newsletter__actions">
							<a class="hp-newsletter__secondary" href="<?php echo esc_url( $x_url ); ?>" target="_blank" rel="noopener noreferrer">Oder auf X folgen</a>
						</div>
					<?php endif; ?>

					<p class="hp-newsletter__footnote">Danach folgt eine Bestätigungs-E-Mail.</p>
				</form>
			</div>
		</div>
	</section>
<?php
}

/**
 * Statuszahlen für die Verwaltung.
 *
 * @return array{active:int,pending:int,unsubscribed:int,total:int}
 */
function hp_get_newsletter_admin_counts(): array {
	global $wpdb;

	$table_name = hp_get_newsletter_table_name();
	$rows       = $wpdb->get_results( "SELECT status, COUNT(*) AS total FROM {$table_name} GROUP BY status", ARRAY_A );
	$counts     = [
		'active'       => 0,
		'pending'      => 0,
		'unsubscribed' => 0,
		'total'        => 0,
	];

	if ( ! is_array( $rows ) ) {
		return $counts;
	}

	foreach ( $rows as $row ) {
		$status = isset( $row['status'] ) ? (string) $row['status'] : '';
		$total  = isset( $row['total'] ) ? (int) $row['total'] : 0;

		if ( isset( $counts[ $status ] ) ) {
			$counts[ $status ] = $total;
			$counts['total']  += $total;
		}
	}

	$counts['unsubscribed'] = hp_get_newsletter_suppression_count();
	$counts['total']       += $counts['unsubscribed'];

	return $counts;
}

/**
 * Management-Seite registrieren.
 */
function hp_register_newsletter_management_page(): void {
	add_submenu_page(
		'hp-contacts',
		'Newsletter',
		'Newsletter',
		'manage_options',
		'hp-newsletter',
		'hp_render_newsletter_management_page'
	);
}
add_action( 'admin_menu', 'hp_register_newsletter_management_page', 20 );

/**
 * CSV-Export der Newsletter-Einträge.
 */
function hp_export_newsletter_subscribers(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Sie haben nicht die erforderlichen Rechte.', 'hasimuener-journal' ) );
	}

	check_admin_referer( 'hp_newsletter_export' );

	global $wpdb;

	$table_name = hp_get_newsletter_table_name();
	$status     = isset( $_GET['status'] ) ? sanitize_key( (string) wp_unslash( $_GET['status'] ) ) : 'active';
	$allowed    = [ 'all', 'active', 'pending', 'unsubscribed' ];

	if ( ! in_array( $status, $allowed, true ) ) {
		$status = 'active';
	}

	if ( 'all' === $status ) {
		$rows = $wpdb->get_results(
			"SELECT email, status, source, source_url, subscribed_at, confirmed_at, unsubscribed_at FROM {$table_name} ORDER BY email ASC",
			ARRAY_A
		);
	} else {
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT email, status, source, source_url, subscribed_at, confirmed_at, unsubscribed_at
				FROM {$table_name}
				WHERE status = %s
				ORDER BY email ASC",
				$status
			),
			ARRAY_A
		);
	}

	if ( ! is_array( $rows ) ) {
		$rows = [];
	}

	nocache_headers();
	header( 'Content-Type: text/csv; charset=UTF-8' );
	header( 'Content-Disposition: attachment; filename="hasimuener-newsletter-' . $status . '-' . gmdate( 'Y-m-d' ) . '.csv"' );

	$output = fopen( 'php://output', 'w' );

	if ( false === $output ) {
		exit;
	}

	fprintf( $output, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) );
	fputcsv( $output, [ 'email', 'status', 'source', 'source_url', 'subscribed_at', 'confirmed_at', 'unsubscribed_at' ] );

	foreach ( $rows as $row ) {
		fputcsv(
			$output,
			[
				(string) ( $row['email'] ?? '' ),
				(string) ( $row['status'] ?? '' ),
				(string) ( $row['source'] ?? '' ),
				(string) ( $row['source_url'] ?? '' ),
				(string) ( $row['subscribed_at'] ?? '' ),
				(string) ( $row['confirmed_at'] ?? '' ),
				(string) ( $row['unsubscribed_at'] ?? '' ),
			]
		);
	}

	fclose( $output );
	exit;
}
add_action( 'admin_post_hp_export_newsletter_subscribers', 'hp_export_newsletter_subscribers' );

/**
 * Rendert die Newsletter-Verwaltung.
 */
function hp_render_newsletter_management_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$counts        = hp_get_newsletter_admin_counts();
	$status_filter = isset( $_GET['status'] ) ? sanitize_key( (string) wp_unslash( $_GET['status'] ) ) : 'all';
	$search        = isset( $_GET['s'] ) ? sanitize_text_field( (string) wp_unslash( $_GET['s'] ) ) : '';
	$notice        = isset( $_GET['notice'] ) ? sanitize_key( (string) wp_unslash( $_GET['notice'] ) ) : '';
	$status_filter = in_array( $status_filter, [ 'all', 'active', 'pending', 'unsubscribed' ], true ) ? $status_filter : 'all';
	$subscribers   = hp_get_recent_newsletter_subscribers( 80, $search, $status_filter );
	$suppressions  = hp_get_recent_newsletter_suppressions( 12 );
	$export_url  = wp_nonce_url(
		add_query_arg(
			[
				'action' => 'hp_export_newsletter_subscribers',
				'status' => 'active',
			],
			admin_url( 'admin-post.php' )
		),
		'hp_newsletter_export'
	);
	$export_all_url = wp_nonce_url(
		add_query_arg(
			[
				'action' => 'hp_export_newsletter_subscribers',
				'status' => 'all',
			],
			admin_url( 'admin-post.php' )
		),
		'hp_newsletter_export'
	);
	?>
	<div class="wrap">
		<h1>Newsletter</h1>
		<p>Lokale Double-Opt-in-Liste für Hinweise auf neue Texte. Die E-Mails selbst laufen serverseitig über denselben Versandweg wie das Kontaktformular.</p>
		<p>Nach einer Austragung bleibt hier nicht die volle Adresse gespeichert, sondern nur eine minimierte Sperrnotiz für die begrenzte Frist.</p>

		<?php if ( 'manual_unsubscribed' === $notice ) : ?>
			<div class="notice notice-success is-dismissible"><p>Die Adresse wurde aus dem Verteiler ausgetragen. Eine Bestätigung wurde versendet.</p></div>
		<?php elseif ( 'missing' === $notice || 'invalid' === $notice ) : ?>
			<div class="notice notice-error is-dismissible"><p>Die gewünschte Newsletter-Adresse konnte nicht gefunden werden.</p></div>
		<?php endif; ?>

		<table class="widefat striped" style="max-width:760px;margin:20px 0;">
			<tbody>
				<tr>
					<td><strong>Aktiv</strong></td>
					<td><?php echo esc_html( (string) $counts['active'] ); ?></td>
				</tr>
				<tr>
					<td><strong>Ausstehend</strong></td>
					<td><?php echo esc_html( (string) $counts['pending'] ); ?></td>
				</tr>
				<tr>
					<td><strong>Abgemeldet</strong></td>
					<td><?php echo esc_html( (string) $counts['unsubscribed'] ); ?></td>
				</tr>
				<tr>
					<td><strong>Gesamt</strong></td>
					<td><?php echo esc_html( (string) $counts['total'] ); ?></td>
				</tr>
			</tbody>
		</table>

		<p>
			<a class="button button-primary" href="<?php echo esc_url( $export_url ); ?>">Aktive Abonnenten als CSV exportieren</a>
			<a class="button" href="<?php echo esc_url( $export_all_url ); ?>">Alle Einträge exportieren</a>
		</p>

		<form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" style="display:flex;flex-wrap:wrap;gap:10px;align-items:end;max-width:980px;margin:20px 0;">
			<input type="hidden" name="page" value="hp-newsletter">
			<p style="margin:0;">
				<label for="hp-newsletter-status" style="display:block;font-weight:600;margin-bottom:6px;">Status</label>
				<select id="hp-newsletter-status" name="status">
					<option value="all"<?php selected( 'all', $status_filter ); ?>>Alle</option>
					<option value="active"<?php selected( 'active', $status_filter ); ?>>Aktiv</option>
					<option value="pending"<?php selected( 'pending', $status_filter ); ?>>Ausstehend</option>
					<option value="unsubscribed"<?php selected( 'unsubscribed', $status_filter ); ?>>Abgemeldet</option>
				</select>
			</p>
			<p style="margin:0;min-width:280px;flex:1 1 320px;">
				<label for="hp-newsletter-search" style="display:block;font-weight:600;margin-bottom:6px;">Suche</label>
				<input id="hp-newsletter-search" class="regular-text" type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="E-Mail oder Quelle">
			</p>
			<p style="margin:0;">
				<button class="button button-secondary" type="submit">Filtern</button>
			</p>
		</form>

		<h2>Letzte Einträge</h2>
		<table class="widefat striped">
			<thead>
				<tr>
					<th>E-Mail</th>
					<th>Status</th>
					<th>Quelle</th>
					<th>Eingetragen</th>
					<th>Bestätigt</th>
					<th>Aktionen</th>
				</tr>
			</thead>
			<tbody>
				<?php if ( $subscribers ) : ?>
					<?php foreach ( $subscribers as $subscriber ) : ?>
						<tr>
							<td><?php echo esc_html( $subscriber['email'] ); ?></td>
							<td><?php echo esc_html( $subscriber['status'] ); ?></td>
							<td><?php echo esc_html( $subscriber['source'] ); ?></td>
							<td><?php echo esc_html( $subscriber['subscribed_at'] ); ?></td>
							<td><?php echo esc_html( $subscriber['confirmed_at'] ); ?></td>
							<td>
								<?php if ( 'unsubscribed' !== $subscriber['status'] ) : ?>
									<a class="button button-small" href="<?php echo esc_url( wp_nonce_url( add_query_arg( [ 'action' => 'hp_admin_unsubscribe_newsletter', 'subscriber' => absint( $subscriber['id'] ) ], admin_url( 'admin-post.php' ) ), 'hp_newsletter_admin_unsubscribe_' . absint( $subscriber['id'] ) ) ); ?>">Abmelden</a>
								<?php else : ?>
									<span>Bereits abgemeldet</span>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php else : ?>
					<tr>
						<td colspan="6">Keine Newsletter-Einträge für diese Auswahl gefunden.</td>
					</tr>
				<?php endif; ?>
			</tbody>
		</table>

		<?php if ( $suppressions ) : ?>
			<h2 style="margin-top:28px;">Zuletzt abgemeldet</h2>
			<p>Aus Datenschutzgründen wird hier nur eine maskierte Darstellung gezeigt.</p>
			<table class="widefat striped">
				<thead>
					<tr>
						<th>Adresse</th>
						<th>Quelle</th>
						<th>Abgemeldet am</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $suppressions as $entry ) : ?>
						<tr>
							<td><?php echo esc_html( $entry['email_mask'] ); ?></td>
							<td><?php echo esc_html( $entry['source'] ); ?></td>
							<td><?php echo esc_html( $entry['unsubscribed_at'] ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>
	<?php
}
