<?php
/**
 * Breadcrumbs — Hasimuener Journal
 *
 * BreadcrumbList JSON-LD Schema (unsichtbar).
 * Gibt Google strukturierte Pfad-Daten für die Suchergebnisse,
 * ohne sichtbare Navigation im Frontend.
 *
 * @package Hasimuener_Journal
 * @since   5.4.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Fuegt ein Breadcrumb-Item nur mit gueltiger URL hinzu.
 *
 * @param array<int,array<string,string>> $items Breadcrumb-Liste.
 * @param string                          $name  Sichtbarer Name.
 * @param mixed                           $url   URL aus einer WP-API.
 * @return void
 */
function hp_breadcrumbs_add_url_item( array &$items, string $name, $url ): void {
	if ( function_exists( 'hp_normalize_public_url' ) ) {
		$normalized = hp_normalize_public_url( $url, '' );
	} else {
		$normalized = ( is_wp_error( $url ) || ! is_string( $url ) ) ? '' : $url;
	}

	if ( '' === $normalized ) {
		$items[] = [ 'name' => $name ];
		return;
	}

	$items[] = [
		'name' => $name,
		'url'  => $normalized,
	];
}

/**
 * Baut die Breadcrumb-Items für den aktuellen Seitenkontext
 * und gibt BreadcrumbList JSON-LD im <head> aus.
 */
function hp_breadcrumbs_schema_output(): void {
	if ( is_front_page() ) {
		return;
	}

	$items = [];

	$items[] = [
		'name' => 'Startseite',
		'url'  => home_url( '/' ),
	];

	if ( is_singular( 'essay' ) ) {
		hp_breadcrumbs_add_url_item( $items, 'Essays', get_post_type_archive_link( 'essay' ) );
		$topics = get_the_terms( get_the_ID(), 'topic' );
		if ( $topics && ! is_wp_error( $topics ) ) {
			hp_breadcrumbs_add_url_item( $items, $topics[0]->name, get_term_link( $topics[0] ) );
		}
		$items[] = [ 'name' => get_the_title() ];

	} elseif ( is_singular( 'note' ) ) {
		hp_breadcrumbs_add_url_item( $items, 'Notizen', get_post_type_archive_link( 'note' ) );
		$topics = get_the_terms( get_the_ID(), 'topic' );
		if ( $topics && ! is_wp_error( $topics ) ) {
			hp_breadcrumbs_add_url_item( $items, $topics[0]->name, get_term_link( $topics[0] ) );
		}
		$items[] = [ 'name' => get_the_title() ];

	} elseif ( is_singular( 'glossar' ) ) {
		hp_breadcrumbs_add_url_item( $items, 'Glossar', get_post_type_archive_link( 'glossar' ) );
		$items[] = [ 'name' => get_the_title() ];

	} elseif ( is_singular( 'dossier' ) ) {
		hp_breadcrumbs_add_url_item( $items, 'Dossiers', get_post_type_archive_link( 'dossier' ) );
		$items[] = [ 'name' => get_the_title() ];

	} elseif ( is_singular( 'page' ) ) {
		$items[] = [ 'name' => get_the_title() ];

	} elseif ( is_post_type_archive( 'essay' ) ) {
		$items[] = [ 'name' => 'Essays' ];

	} elseif ( is_post_type_archive( 'note' ) ) {
		$items[] = [ 'name' => 'Notizen' ];

	} elseif ( is_post_type_archive( 'glossar' ) ) {
		$items[] = [ 'name' => 'Glossar' ];

	} elseif ( is_post_type_archive( 'dossier' ) ) {
		$items[] = [ 'name' => 'Dossiers' ];

	} elseif ( is_tax( 'topic' ) ) {
		$items[] = [ 'name' => 'Themenfelder' ];
		$items[] = [ 'name' => single_term_title( '', false ) ];

	} elseif ( is_search() ) {
		$items[] = [ 'name' => 'Suche' ];

	} elseif ( is_404() ) {
		$items[] = [ 'name' => '404' ];
	}

	if ( count( $items ) < 2 ) {
		return;
	}

	// JSON-LD ausgeben
	$list_items = [];
	foreach ( $items as $i => $item ) {
		$entry = [
			'@type'    => 'ListItem',
			'position' => $i + 1,
			'name'     => $item['name'],
		];
		if ( isset( $item['url'] ) ) {
			$entry['item'] = $item['url'];
		}
		$list_items[] = $entry;
	}

	$schema = [
		'@context'        => 'https://schema.org',
		'@type'           => 'BreadcrumbList',
		'itemListElement' => $list_items,
	];

	echo "\n<!-- Hasim Üner: BreadcrumbList JSON-LD -->\n";
	echo '<script type="application/ld+json">';
	echo wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
	echo "</script>\n";
}
add_action( 'wp_head', 'hp_breadcrumbs_schema_output', 6 );
