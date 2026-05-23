<?php
/**
 * SEO-Hygiene — Hasimuener Journal
 *
 * Macro-Layer-Fixes zur Sichtbarkeitssteigerung ohne neuen Content:
 * - Robots-Steuerung (noindex für Suche, 404, paginierte Archive, Attachments)
 * - Attachment-Pages → Parent-Redirect (Duplicate-Content-Vermeidung)
 * - Autoren-Archive → Home-Redirect (single-author Setup)
 * - hreflang self-referential für `de`
 * - wp_head-Bereinigung: wlwmanifest, RSD, Generator, Kategorien-Feeds, X-Pingback
 *
 * @package Hasimuener_Journal
 * @since   5.5.0
 */

defined( 'ABSPATH' ) || exit;

/* =========================================
   1. ROBOTS — noindex für Low-Value-Seiten
   ========================================= */

/**
 * Setzt noindex/follow auf Seiten, die nicht im Index landen sollen:
 * - Suchergebnisse
 * - 404
 * - Paginierte Archive ab Seite 2 (Duplicate-Content)
 * - Attachment-Pages
 *
 * @param array<string,bool> $robots wp_robots-Direktiven.
 * @return array<string,bool>
 */
function hp_seo_robots( array $robots ): array {
	if ( is_search() || is_404() || is_attachment() ) {
		$robots['noindex']  = true;
		$robots['follow']   = true;
		unset( $robots['index'] );
		return $robots;
	}

	if ( is_paged() ) {
		$robots['noindex'] = true;
		$robots['follow']  = true;
		unset( $robots['index'] );
	}

	return $robots;
}
add_filter( 'wp_robots', 'hp_seo_robots' );

/* =========================================
   2. ATTACHMENT-PAGES → PARENT
   ========================================= */

/**
 * Leitet Attachment-Pages 301 auf den Parent-Post um.
 *
 * Attachment-Pages sind dünne Duplicate-Content-Seiten —
 * Google indexiert sie sonst neben den eigentlichen Beiträgen.
 */
function hp_redirect_attachment_pages(): void {
	if ( ! is_attachment() ) {
		return;
	}

	$post = get_queried_object();
	if ( ! ( $post instanceof WP_Post ) ) {
		return;
	}

	$target = $post->post_parent
		? get_permalink( $post->post_parent )
		: home_url( '/' );

	wp_safe_redirect( $target, 301 );
	exit;
}
add_action( 'template_redirect', 'hp_redirect_attachment_pages' );

/* =========================================
   3. AUTOREN-ARCHIVE DEAKTIVIEREN
   ========================================= */

/**
 * Leitet Autoren-Archive 301 auf die Startseite um.
 * Single-author Setup → Autoren-Archiv ist redundant zur Home/Essays.
 */
function hp_disable_author_archives(): void {
	if ( ! is_author() ) {
		return;
	}

	wp_safe_redirect( home_url( '/' ), 301 );
	exit;
}
add_action( 'template_redirect', 'hp_disable_author_archives' );

/**
 * Blockiert die `?author=N`-Enumeration (gängiger WP-Recon-Vektor
 * UND SEO-Duplicate). Greift nur im Frontend.
 */
function hp_block_author_query(): void {
	if ( is_admin() ) {
		return;
	}

	if ( isset( $_GET['author'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}
}
add_action( 'template_redirect', 'hp_block_author_query', 1 );

/* =========================================
   4. HREFLANG
   ========================================= */

/**
 * Gibt hreflang-Tags aus: self-referential `de` + `x-default`.
 *
 * Auch monolingual ist hreflang ein klares Sprach-Signal an Google
 * und reduziert SERP-Verwechslungen mit anderssprachigen Domains.
 */
function hp_output_hreflang(): void {
	if ( is_404() || is_search() ) {
		return;
	}

	$url  = function_exists( 'hp_get_current_url' ) ? hp_get_current_url() : home_url( '/' );
	$lang = substr( get_locale(), 0, 2 ) ?: 'de';

	printf( '<link rel="alternate" hreflang="%s" href="%s" />' . "\n", esc_attr( $lang ), esc_url( $url ) );
	printf( '<link rel="alternate" hreflang="x-default" href="%s" />' . "\n", esc_url( $url ) );
}
add_action( 'wp_head', 'hp_output_hreflang', 4 );

/* =========================================
   5. WP_HEAD-BEREINIGUNG
   ========================================= */

/**
 * Entfernt veraltete/nutzlose Tags aus dem <head>:
 * - wlwmanifest_link        (Windows Live Writer — obsolet)
 * - rsd_link                 (Really Simple Discovery — XML-RPC)
 * - wp_generator             (verrät WP-Version)
 * - feed_links_extra         (Kategorien-/Kommentar-Feeds → Crawl-Noise)
 * - wp_shortlink_wp_head     (Shortlink → potenzielle Canonical-Konkurrenz)
 *
 * Behält bewusst: feed_links (Haupt-Feed) — wird auch von hp_rss_feed_links()
 * abgedeckt, aber unschädlich.
 */
function hp_clean_wp_head(): void {
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'feed_links_extra', 3 );
	remove_action( 'wp_head', 'wp_shortlink_wp_head', 10 );
}
add_action( 'init', 'hp_clean_wp_head' );

/**
 * Entfernt den X-Pingback-Response-Header.
 * Spart Bytes pro Request und reduziert Pingback-Spam-Surface.
 *
 * @param array<string,string> $headers
 * @return array<string,string>
 */
function hp_remove_pingback_header( array $headers ): array {
	unset( $headers['X-Pingback'] );
	return $headers;
}
add_filter( 'wp_headers', 'hp_remove_pingback_header' );
