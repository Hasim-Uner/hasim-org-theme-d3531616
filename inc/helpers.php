<?php
/**
 * Hilfsfunktionen — Hasimuener Journal
 *
 * Wiederverwendbare Utility-Funktionen: Lesedauer, Body-Klassen,
 * Flush-Logik. Keine Hooks auf Top-Level — nur Definitionen.
 * Hooks werden am Ende der Datei registriert.
 *
 * @package Hasimuener_Journal
 * @since   4.0.0
 */

defined( 'ABSPATH' ) || exit;

/* -----------------------------------------
   URL-Helfer
   ----------------------------------------- */

/**
 * Normalisiert eine WordPress-URL-Rueckgabe auf einen sicheren String.
 *
 * WP-APIs wie `get_term_link()` koennen `WP_Error` liefern; SEO-, Schema-
 * und Breadcrumb-Ausgaben brauchen aber immer druckbare URL-Strings.
 *
 * @param mixed       $url      URL-Rueckgabe einer WP-API.
 * @param string|null $fallback Fallback-URL. `null` nutzt die Startseite.
 * @return string
 */
function hp_normalize_public_url( $url, ?string $fallback = null ): string {
	if ( is_wp_error( $url ) || ! is_string( $url ) || '' === $url ) {
		return null === $fallback ? home_url( '/' ) : $fallback;
	}

	return $url;
}

/* -----------------------------------------
   Lesedauer
   ----------------------------------------- */

/**
 * Geschätzte Lesedauer eines Beitrags.
 *
 * 200 Wörter/Min. — konservativ kalkuliert für
 * anspruchsvolle Langform-Texte mit Fachvokabular.
 *
 * @param int|null $post_id Post-ID (default: aktueller Post im Loop).
 * @return string  z. B. "4 Min. Lesezeit"
 */
function hp_reading_time( ?int $post_id = null ): string {
	$post_id = $post_id ?: get_the_ID();
	$content = get_post_field( 'post_content', $post_id );
	$words   = str_word_count( wp_strip_all_tags( $content ) );
	$minutes = max( 1, (int) ceil( $words / 200 ) );

	return sprintf( '%d Min. Lesezeit', $minutes );
}

/* -----------------------------------------
   Body-Klassen
   ----------------------------------------- */

/**
 * Erweitert die Body-Klassen um semantische Kontextklassen.
 *
 * Ermöglicht CSS-Targeting ohne Abhängigkeit von
 * WordPress-generierten Klassen, die sich bei Updates
 * ändern können.
 *
 * @param string[] $classes Bestehende Body-Klassen.
 * @return string[]
 */
function hp_custom_body_classes( array $classes ): array {

	if ( is_singular( 'essay' ) ) {
		$classes[] = 'single-essay';
		$classes[] = 'editorial-longform';
	}

	if ( is_singular( 'note' ) ) {
		$classes[] = 'single-note';
		$classes[] = 'editorial-short';
	}

	if ( is_front_page() ) {
		$classes[] = 'journal-home';
	}

	if ( is_post_type_archive( 'essay' ) ) {
		$classes[] = 'archive-essays';
	}

	if ( is_post_type_archive( 'note' ) ) {
		$classes[] = 'archive-notes';
	}

	if ( is_singular( 'glossar' ) ) {
		$classes[] = 'single-glossar';
	}

	if ( is_post_type_archive( 'glossar' ) ) {
		$classes[] = 'archive-glossar';
	}

	if ( is_singular( 'dossier' ) ) {
		$classes[] = 'single-dossier';
	}

	if ( is_post_type_archive( 'dossier' ) ) {
		$classes[] = 'archive-dossiers';
	}

	if ( is_page( 'wissensgraph' ) ) {
		$classes[] = 'page-wissensgraph';
	}

	return $classes;
}
add_filter( 'body_class', 'hp_custom_body_classes' );

/* -----------------------------------------
   CPTs in WordPress-Suche einbinden
   ----------------------------------------- */

/**
 * Erweitert die Standard-Suche um alle Custom Post Types.
 *
 * @param WP_Query $query Die aktuelle Abfrage.
 */
function hp_extend_search_to_cpts( $query ): void {
	if ( $query->is_search() && $query->is_main_query() && ! is_admin() ) {
		$query->set( 'post_type', [ 'essay', 'note', 'glossar', 'page' ] );
	}
}
add_action( 'pre_get_posts', 'hp_extend_search_to_cpts' );

/* -----------------------------------------
   Rewrite Flush (Theme-Aktivierung)
   ----------------------------------------- */

/**
 * Spült Rewrite-Rules nach Theme-Wechsel.
 *
 * Wird über after_switch_theme getriggert — stellt sicher,
 * dass CPT-Slugs und Taxonomy-Rewrites sofort funktionieren,
 * ohne manuellen Permalink-Save im Admin.
 */
function hp_flush_rewrite_rules(): void {
	hp_register_post_types();
	hp_register_glossar_cpt();
	hp_register_taxonomies();
	flush_rewrite_rules();

	hp_create_default_topics();
}
add_action( 'after_switch_theme', 'hp_flush_rewrite_rules' );
