<?php
/**
 * Contact page bootstrap helpers.
 *
 * @package Hasimuener_Journal
 */


defined( 'ABSPATH' ) || exit;

/**
 * Liefert die gespeicherte Kontakt-Seite, falls vorhanden.
 */
function hp_get_contact_page_id(): int {
	$page_id = (int) get_option( 'hp_contact_page_id', 0 );

	if ( $page_id > 0 && 'page' === get_post_type( $page_id ) ) {
		hp_assign_contact_page_template( $page_id );
		return $page_id;
	}

	$page = get_page_by_path( 'kontakt', OBJECT, 'page' );

	if ( $page instanceof WP_Post ) {
		hp_assign_contact_page_template( (int) $page->ID );
		update_option( 'hp_contact_page_id', (int) $page->ID, false );
		return (int) $page->ID;
	}

	return 0;
}

/**
 * Stellt sicher, dass die Kontaktseite das richtige Template nutzt.
 */
function hp_assign_contact_page_template( int $page_id ): void {
	if ( $page_id <= 0 ) {
		return;
	}

	if ( 'page-kontakt.php' !== get_page_template_slug( $page_id ) ) {
		update_post_meta( $page_id, '_wp_page_template', 'page-kontakt.php' );
	}

	$page = get_post( $page_id );

	if ( $page instanceof WP_Post && in_array( $page->post_title, [ '', 'Kontakt' ], true ) ) {
		wp_update_post(
			[
				'ID'         => $page_id,
				'post_title' => hp_get_contact_page_title(),
			]
		);
	}
}

/**
 * Liefert die URL der Kontaktseite.
 */
function hp_get_contact_page_url(): string {
	$page_id = hp_get_contact_page_id();

	if ( $page_id > 0 ) {
		$permalink = get_permalink( $page_id );

		if ( is_string( $permalink ) && '' !== $permalink ) {
			return $permalink;
		}
	}

	return home_url( '/kontakt/' );
}

/**
 * Legt die Kontaktseite einmalig an, falls sie fehlt.
 */
function hp_bootstrap_contact_page(): void {
	if ( wp_installing() || ! post_type_exists( 'page' ) ) {
		return;
	}

	if ( hp_get_contact_page_id() > 0 ) {
		return;
	}

	$slug_conflict = get_posts( [
		'name'              => 'kontakt',
		'post_type'         => 'any',
		'post_status'       => [ 'publish', 'future', 'draft', 'pending', 'private' ],
		'fields'            => 'ids',
		'posts_per_page'    => 1,
		'suppress_filters'  => true,
		'no_found_rows'     => true,
		'ignore_sticky_posts' => true,
	] );

	if ( ! empty( $slug_conflict ) ) {
		return;
	}

	$page_id = wp_insert_post( [
		'post_type'      => 'page',
		'post_status'    => 'publish',
		'post_title'     => hp_get_contact_page_title(),
		'post_name'      => 'kontakt',
		'post_content'   => '',
		'comment_status' => 'closed',
		'ping_status'    => 'closed',
	] );

	if ( ! is_wp_error( $page_id ) && $page_id > 0 ) {
		hp_assign_contact_page_template( (int) $page_id );
		update_option( 'hp_contact_page_id', (int) $page_id, false );
	}
}
add_action( 'init', 'hp_bootstrap_contact_page', 25 );
