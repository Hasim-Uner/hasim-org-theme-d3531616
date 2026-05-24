<?php
/**
 * SEO Cockpit runtime diagnostics.
 *
 * @package Hasimuener_Journal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Build one diagnostic row.
 *
 * @param string $area    Area label.
 * @param string $label   Check label.
 * @param string $status  Check status.
 * @param string $message Check message.
 * @return array<string, string>
 */
function nexus_build_seo_cockpit_diagnostic( $area, $label, $status, $message ) {
	return [
		'area'    => sanitize_key( (string) $area ),
		'label'   => trim( wp_strip_all_tags( (string) $label ) ),
		'status'  => sanitize_key( (string) $status ),
		'message' => trim( wp_strip_all_tags( (string) $message ) ),
	];
}

/**
 * Return compact runtime diagnostics for the cockpit.
 *
 * @param string $detail_url Optional detail URL.
 * @return array<string, mixed>
 */
function nexus_get_seo_cockpit_diagnostics( $detail_url = '' ) {
	$config      = nexus_get_seo_cockpit_search_console_config();
	$tokens      = nexus_get_seo_cockpit_tokens();
	$runtime     = nexus_get_seo_cockpit_runtime_summary();
	$lock        = get_transient( nexus_get_seo_cockpit_sync_lock_key() );
	$detail_url  = nexus_normalize_seo_cockpit_url( $detail_url );
	$checks      = [];
	$access_test = null;
	$koko        = nexus_get_koko_analytics_status();
	$link_graph  = function_exists( 'nexus_get_seo_cockpit_internal_link_graph' ) ? nexus_get_seo_cockpit_internal_link_graph() : [];

	$checks[] = nexus_build_seo_cockpit_diagnostic(
		'oauth',
		'Property',
		'' !== $config['property'] ? 'ok' : 'warning',
		'' !== $config['property'] ? 'Property ist gesetzt.' : 'Es ist noch keine Property hinterlegt.'
	);

	$checks[] = nexus_build_seo_cockpit_diagnostic(
		'oauth',
		'Client-Konfiguration',
		( '' !== $config['client_id'] && '' !== $config['client_secret'] ) ? 'ok' : 'warning',
		( '' !== $config['client_id'] && '' !== $config['client_secret'] ) ? 'Client ID und Secret sind vorhanden.' : 'Client ID oder Secret fehlen noch.'
	);

	if ( ! nexus_current_user_can_manage_seo_cockpit() ) {
		$access_test = nexus_build_seo_cockpit_diagnostic(
			'oauth',
			'Access Token',
			'warning',
			'Die aktive Token-Prüfung ist nur für Verwalter sichtbar.'
		);
	} elseif ( '' !== (string) ( $tokens['access_token'] ?? '' ) ) {
		$access = nexus_get_seo_cockpit_access_token();
		if ( is_wp_error( $access ) ) {
			$access_test = nexus_build_seo_cockpit_diagnostic(
				'oauth',
				'Access Token',
				'error',
				$access->get_error_message()
			);
		} else {
			$access_test = nexus_build_seo_cockpit_diagnostic(
				'oauth',
				'Access Token',
				'ok',
				'Access Token ist vorhanden und nutzbar.'
			);
		}
	} else {
		$access_test = nexus_build_seo_cockpit_diagnostic(
			'oauth',
			'Access Token',
			'warning',
			'Es ist noch kein Access Token gespeichert.'
		);
	}

	$checks[] = $access_test;

	$checks[] = nexus_build_seo_cockpit_diagnostic(
		'sync',
		'Cron-Event',
		! empty( $runtime['next_sync_at'] ) ? 'ok' : 'error',
		! empty( $runtime['next_sync_at'] ) ? 'Der nächste Sync ist geplant.' : 'Es ist aktuell kein nächster Sync geplant.'
	);

	$checks[] = nexus_build_seo_cockpit_diagnostic(
		'sync',
		'Sync-Lock',
		( is_array( $lock ) && ! empty( $lock['expires_at'] ) && (int) $lock['expires_at'] > time() ) ? 'warning' : 'ok',
		( is_array( $lock ) && ! empty( $lock['expires_at'] ) && (int) $lock['expires_at'] > time() )
			? 'Es existiert aktuell ein aktiver Sync-Lock.'
			: 'Kein aktiver Sync-Lock vorhanden.'
	);

	$checks[] = nexus_build_seo_cockpit_diagnostic(
		'sync',
		'Letzter Sync-Status',
		'success' === (string) ( $runtime['last_sync_status'] ?? '' ) ? 'ok' : ( ! empty( $runtime['last_sync_status'] ) ? 'warning' : 'warning' ),
		! empty( $runtime['last_sync_status'] )
			? sprintf( 'Letzter Status: %s', (string) $runtime['last_sync_status'] )
			: 'Es liegt noch kein Sync-Status vor.'
	);

	$checks[] = nexus_build_seo_cockpit_diagnostic(
		'koko',
		'Koko Analytics',
		! empty( $koko['rest_available'] ) ? 'ok' : ( ! empty( $koko['active'] ) ? 'warning' : 'warning' ),
		(string) ( $koko['label'] ?? 'Koko-Status unbekannt.' )
	);

	$checks[] = nexus_build_seo_cockpit_diagnostic(
		'links',
		'Interner Linkgraph',
		! empty( $link_graph['built_at'] ) ? 'ok' : 'warning',
		! empty( $link_graph['built_at'] )
			? sprintf( 'Linkgraph wurde aus %d veröffentlichten Inhalten aufgebaut.', (int) ( $link_graph['post_count'] ?? 0 ) )
			: 'Linkgraph wurde noch nicht aufgebaut.'
	);

	if ( '' !== $detail_url ) {
		$context = nexus_get_seo_cockpit_wp_context_for_url( $detail_url );

		$checks[] = nexus_build_seo_cockpit_diagnostic(
			'drilldown',
			'URL-Normalisierung',
			'' !== $detail_url ? 'ok' : 'error',
			'' !== $detail_url ? 'Detail-URL wurde erfolgreich normalisiert.' : 'Die Detail-URL ist leer oder ungültig.'
		);

		$checks[] = nexus_build_seo_cockpit_diagnostic(
			'drilldown',
			'WordPress-Kontext',
			! empty( $context['resolved'] ) ? 'ok' : 'warning',
			! empty( $context['resolved'] ) ? 'Die Detail-URL konnte einem WordPress-Kontext zugeordnet werden.' : 'Die Detail-URL ist derzeit keinem klaren WordPress-Objekt zugeordnet.'
		);

		$checks[] = nexus_build_seo_cockpit_diagnostic(
			'drilldown',
			'Detail-Cache',
			'' !== nexus_get_seo_cockpit_cache_key( 'detail', [ nexus_get_seo_cockpit_property(), nexus_get_seo_cockpit_requested_range_days(), $detail_url ] ) ? 'ok' : 'error',
			'Ein Detail-Cache-Key kann für diese URL erzeugt werden.'
		);
	}

	$summary = [
		'ok'      => 0,
		'warning' => 0,
		'error'   => 0,
	];

	foreach ( $checks as $check ) {
		if ( isset( $summary[ $check['status'] ] ) ) {
			$summary[ $check['status'] ]++;
		}
	}

	return [
		'checks'  => $checks,
		'summary' => $summary,
	];
}
