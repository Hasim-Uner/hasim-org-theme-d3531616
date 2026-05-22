<?php
/**
 * GeneratePress-Kompatibilität — Hasimuener Journal
 *
 * Unterdrückt die GP-eigene Ausgabe von Datum, Autor und
 * Post-Meta auf Custom Post Types (essay, note).
 *
 * Drei Strategien werden parallel eingesetzt, weil GP
 * je nach Version und Premium-Modul unterschiedliche
 * Hooks/Filter nutzt:
 *
 * 1) remove_action — Standard-Hooks entfernen
 * 2) Output-Filter — leeren String zurückgeben
 * 3) Meta-Items-Filter — Arrays leeren
 *
 * CSS-Fallback (.single-essay .entry-meta { display:none })
 * liegt zusätzlich in style.css als Nuclear Option.
 *
 * @package Hasimuener_Journal
 * @since   4.0.0
 */

defined( 'ABSPATH' ) || exit;

/* -----------------------------------------
   Strategie 1: Hooks entfernen
   ----------------------------------------- */

/**
 * Entfernt GP-Meta-Hooks auf essay/note Singles.
 *
 * Muss auf 'wp' laufen (nicht 'init'), weil is_singular()
 * erst nach Query-Setup verfügbar ist. Mehrere Prioritäten
 * werden abgedeckt, da GP-Premium diese variiert.
 */
function hp_disable_gp_post_meta(): void {
	if ( ! is_singular( [ 'essay', 'note', 'glossar' ] ) ) {
		return;
	}

	foreach ( [ 10, 15, 20, 30 ] as $prio ) {
		remove_action( 'generate_after_entry_title', 'generate_post_meta', $prio );
		remove_action( 'generate_after_entry_content', 'generate_footer_meta', $prio );
		remove_action( 'generate_after_entry_title', 'generate_entry_meta_header', $prio );
	}
}
add_action( 'wp', 'hp_disable_gp_post_meta' );

/* -----------------------------------------
   Strategie 2: Output-Filter
   ----------------------------------------- */

/**
 * GP-Output-Filter: Gibt leeren String für Meta-Elemente
 * zurück, wenn ein CPT-Single angezeigt wird.
 *
 * @param string $output GP-generierter HTML-String.
 * @return string Leer auf CPT-Singles, sonst original.
 */
function hp_suppress_gp_meta_on_cpt( string $output ): string {
	if ( is_singular( [ 'essay', 'note', 'glossar' ] ) ) {
		return '';
	}
	return $output;
}
add_filter( 'generate_post_date_output', 'hp_suppress_gp_meta_on_cpt' );
add_filter( 'generate_post_author_output', 'hp_suppress_gp_meta_on_cpt' );
add_filter( 'generate_post_categories_output', 'hp_suppress_gp_meta_on_cpt' );
add_filter( 'generate_post_tags_output', 'hp_suppress_gp_meta_on_cpt' );
add_filter( 'generate_post_comments_link_output', 'hp_suppress_gp_meta_on_cpt' );

/* -----------------------------------------
   Strategie 3: Meta-Items-Array leeren
   ----------------------------------------- */

/**
 * Leert die GP-Meta-Item-Arrays auf CPT-Singles.
 *
 * GP iteriert über diese Arrays, um Meta-Elemente zu rendern.
 * Leeres Array → keine Ausgabe.
 *
 * @param array $items Meta-Items-Array.
 * @return array Leer auf CPT-Singles, sonst original.
 */
function hp_strip_gp_meta_items( $items ) {
	if ( is_singular( [ 'essay', 'note', 'glossar' ] ) ) {
		return [];
	}
	return $items;
}
add_filter( 'generate_header_entry_meta_items', 'hp_strip_gp_meta_items' );
add_filter( 'generate_footer_entry_meta_items', 'hp_strip_gp_meta_items' );

/* -----------------------------------------
   GP-Footer-Credits entfernen (RSS-Link)
   ----------------------------------------- */

/**
 * Leert die GP-Copyright/Credits-Zeile im Footer.
 * Entfernt den sichtbaren RSS-Link.
 */
function hp_remove_gp_credits( $output ) {
	return '';
}
add_filter( 'generate_copyright', 'hp_remove_gp_credits' );

/* -----------------------------------------
   GP-Sidebar auf CPT-Templates abschalten
   -----------------------------------------
   Dossier/Glossar/Essay/Note nutzen volle Breite
   und eigene Hero-Layouts. Eine GP-Sidebar würde
   den Content-Bereich asymmetrisch verkleinern
   und die zentrierten Heroes off-center wirken
   lassen (linksbündig-Bug, beobachtet auf
   Dossier-Singles im Mai 2026).

   Sidebar-Layout 'no-sidebar' wird per Filter
   erzwungen — überstimmt GP-Customizer-Default
   sowie postmeta-basierte Per-Post-Layouts. */
add_filter( 'generate_sidebar_layout', function ( $layout ) {
	if ( is_singular( [ 'essay', 'note', 'glossar', 'dossier' ] )
		|| is_post_type_archive( [ 'essay', 'note', 'glossar', 'dossier' ] ) ) {
		return 'no-sidebar';
	}
	return $layout;
} );
