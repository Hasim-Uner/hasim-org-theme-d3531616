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

/* =========================================
   6. IMAGE-ALT-FALLBACK
   ========================================= */

/**
 * Setzt automatisch ein `alt`-Attribut, wenn das Bild keins hat.
 *
 * Fallback-Kette:
 * 1. vorhandenes alt
 * 2. Bild-Titel (Attachment-Post-Title)
 * 3. Bild-Caption
 * 4. Titel des Parent-Posts
 *
 * Wirkt nur, wenn alt fehlt — überschreibt nie redaktionelle alts.
 *
 * @param array<string,string> $attr       Bestehende Attribute.
 * @param WP_Post              $attachment Attachment-Post.
 * @return array<string,string>
 */
function hp_image_alt_fallback( $attr, $attachment ): array {
	if ( ! empty( $attr['alt'] ) ) {
		return $attr;
	}

	if ( ! ( $attachment instanceof WP_Post ) ) {
		return $attr;
	}

	$alt = trim( (string) get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true ) );

	if ( ! $alt ) {
		$alt = trim( (string) $attachment->post_title );
	}

	if ( ! $alt ) {
		$alt = trim( (string) $attachment->post_excerpt );
	}

	if ( ! $alt && $attachment->post_parent ) {
		$alt = trim( (string) get_the_title( $attachment->post_parent ) );
	}

	if ( $alt ) {
		$attr['alt'] = $alt;
	}

	return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'hp_image_alt_fallback', 10, 2 );

/* =========================================
   7. LAST-MODIFIED HEADER (Singles)
   ========================================= */

/**
 * Setzt `Last-Modified` und `ETag` auf singulären Inhalten.
 *
 * Erhöht die Crawl-Effizienz: Google darf mit `If-Modified-Since`
 * antworten und spart Roundtrips — das Crawl-Budget fließt
 * stattdessen in neue/aktualisierte Inhalte.
 */
function hp_send_last_modified_header(): void {
	if ( is_admin() || ! is_singular() ) {
		return;
	}

	$post = get_queried_object();
	if ( ! ( $post instanceof WP_Post ) ) {
		return;
	}

	$modified_gmt = get_post_modified_time( 'U', true, $post );
	if ( ! $modified_gmt ) {
		return;
	}

	$last_modified = gmdate( 'D, d M Y H:i:s', $modified_gmt ) . ' GMT';
	$etag          = '"' . md5( $modified_gmt . '-' . $post->ID ) . '"';

	header( 'Last-Modified: ' . $last_modified );
	header( 'ETag: ' . $etag );

	$ims  = $_SERVER['HTTP_IF_MODIFIED_SINCE'] ?? '';
	$inm  = $_SERVER['HTTP_IF_NONE_MATCH']     ?? '';
	$hit  = ( $ims && strtotime( $ims ) >= $modified_gmt )
		|| ( $inm && trim( $inm ) === $etag );

	if ( $hit ) {
		status_header( 304 );
		exit;
	}
}
add_action( 'template_redirect', 'hp_send_last_modified_header' );

/* =========================================
   8. REL="ME" IDENTITY-LINKS
   ========================================= */

/**
 * Gibt `<link rel="me">` für ORCID und X aus.
 *
 * Verifiziert die Author-Identität für IndieWeb-Konsumenten
 * und stärkt sameAs/Person-Schema durch HTML-Microformats.
 */
function hp_output_rel_me(): void {
	$orcid_url = defined( 'HP_ORCID_URL' ) ? HP_ORCID_URL : '';

	if ( $orcid_url ) {
		printf( '<link rel="me" href="%s" />' . "\n", esc_url( $orcid_url ) );
	}

	printf( '<link rel="me" href="%s" />' . "\n", esc_url( 'https://x.com/_0239983326111' ) );
}
add_action( 'wp_head', 'hp_output_rel_me', 5 );
