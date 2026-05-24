<?php
/**
 * Newsletter subscriber storage and suppression helpers.
 *
 * @package Hasimuener_Journal
 */


defined( 'ABSPATH' ) || exit;

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
