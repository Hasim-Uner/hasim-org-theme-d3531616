<?php
/**
 * Newsletter admin queries and cleanup helpers.
 *
 * @package Hasimuener_Journal
 */


defined( 'ABSPATH' ) || exit;

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
