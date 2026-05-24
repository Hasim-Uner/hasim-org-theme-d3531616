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

	// Leere Taxonomie-Archive (z. B. neu angelegte Topics ohne Posts):
	// Soft-404-Risiko in Google Search Console. Lieber gleich noindex,
	// follow, damit Crawl-Budget nicht für Leerhülsen draufgeht.
	if ( is_tax() ) {
		$term = get_queried_object();
		if ( $term instanceof WP_Term && (int) $term->count === 0 ) {
			$robots['noindex'] = true;
			$robots['follow']  = true;
			unset( $robots['index'] );
		}
	}

	// Leere CPT-Archive (kein einziger publizierter Beitrag):
	if ( is_post_type_archive() ) {
		global $wp_query;
		if ( $wp_query instanceof WP_Query && (int) $wp_query->found_posts === 0 ) {
			$robots['noindex'] = true;
			$robots['follow']  = true;
			unset( $robots['index'] );
		}
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
   2b. LEGACY TOPIC-SLUG REDIRECTS
   ========================================= */

/**
 * Leitet alte Topic-Term-URLs 301 auf die aktuellen Slugs um.
 *
 * Greift, wenn ein 404 auf `/thema/<alt-slug>/` ausgelöst würde und
 * der alte Slug in `hp_get_legacy_topic_redirect_map()` steht.
 * Bewahrt eingehende Backlinks und Indexsignale bei Restrukturierungen.
 */
function hp_redirect_legacy_topic_urls(): void {
	if ( ! is_404() || ! function_exists( 'hp_get_legacy_topic_redirect_map' ) ) {
		return;
	}

	$path = isset( $_SERVER['REQUEST_URI'] )
		? wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH )
		: '';

	if ( ! $path || ! preg_match( '#^/thema/([^/]+)/?$#', (string) $path, $m ) ) {
		return;
	}

	$old_slug = sanitize_title( $m[1] );
	$map      = hp_get_legacy_topic_redirect_map();

	if ( ! isset( $map[ $old_slug ] ) ) {
		return;
	}

	$target = get_term_by( 'slug', $map[ $old_slug ], 'topic' );
	if ( ! $target ) {
		return;
	}

	$target_url = get_term_link( $target );
	if ( is_wp_error( $target_url ) ) {
		return;
	}

	wp_safe_redirect( $target_url, 301 );
	exit;
}
add_action( 'template_redirect', 'hp_redirect_legacy_topic_urls', 5 );

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

/* =========================================
   9. FRONTEND-PERFORMANCE (CWV)
   ========================================= */

/**
 * Entfernt das WP-Emoji-Script + zugehörige Styles im Frontend.
 *
 * Spart ~10 KB JS, einen Inline-Script-Block und einen externen
 * Render-Pfad — moderne Browser rendern Emojis nativ.
 */
function hp_disable_wp_emojis(): void {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );

	add_filter( 'tiny_mce_plugins', static function ( $plugins ) {
		return is_array( $plugins ) ? array_diff( $plugins, [ 'wpemoji' ] ) : $plugins;
	} );

	add_filter( 'emoji_svg_url', '__return_false' );
}
add_action( 'init', 'hp_disable_wp_emojis' );

/**
 * Entfernt das wp-embed.min.js im Frontend.
 *
 * Wird nur für oEmbed-iframe-Resizing fremder Seiten gebraucht —
 * im redaktionellen Setup hier nicht relevant. Spart ~2 KB JS.
 */
function hp_disable_wp_embed(): void {
	wp_deregister_script( 'wp-embed' );
}
add_action( 'wp_footer', 'hp_disable_wp_embed', 1 );

/**
 * Entfernt jQuery-Migrate vom Frontend (Admin bleibt unangetastet).
 *
 * Migrate ist nur für Legacy-Plugins nötig — eigene Scripts laufen
 * ohne. Spart ~10 KB JS + einen Parse-Pass.
 *
 * @param WP_Scripts $scripts
 */
function hp_remove_jquery_migrate( $scripts ): void {
	if ( is_admin() || ! ( $scripts instanceof WP_Scripts ) ) {
		return;
	}

	if ( ! empty( $scripts->registered['jquery'] ) ) {
		$jquery = $scripts->registered['jquery'];
		if ( ! empty( $jquery->deps ) ) {
			$jquery->deps = array_diff( $jquery->deps, [ 'jquery-migrate' ] );
		}
	}
}
add_action( 'wp_default_scripts', 'hp_remove_jquery_migrate' );

/**
 * Gibt dns-prefetch- und preconnect-Hinweise für externe Domains aus.
 *
 * x.com + orcid.org sind im Footer/sameAs verlinkt — frühe DNS-Auflösung
 * spart Latenz, wenn Nutzer:innen den Links folgen.
 *
 * @param array<int,string> $hints
 * @param string            $relation
 * @return array<int,string>
 */
function hp_resource_hints( array $hints, string $relation ): array {
	if ( 'dns-prefetch' === $relation ) {
		$hints[] = '//x.com';
		$hints[] = '//orcid.org';
	}

	return $hints;
}
add_filter( 'wp_resource_hints', 'hp_resource_hints', 10, 2 );

/* =========================================
   10. REL="ME" IDENTITY-LINKS
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

/* =========================================
   11. PINGBACKS / TRACKBACKS / XML-RPC
   ========================================= */

/**
 * Schaltet XML-RPC im Frontend komplett ab.
 *
 * Reduziert Angriffsfläche (Brute-Force, Pingback-DDoS) und
 * spart minimal Bytes — kein moderner Workflow nutzt es noch.
 */
add_filter( 'xmlrpc_enabled', '__return_false' );

/**
 * Entfernt XML-RPC- und Pingback-Header-Endpunkte.
 *
 * @param array<string,string> $methods
 * @return array<string,string>
 */
function hp_disable_xmlrpc_methods( array $methods ): array {
	unset( $methods['pingback.ping'], $methods['pingback.extensions.getPingbacks'] );
	return $methods;
}
add_filter( 'xmlrpc_methods', 'hp_disable_xmlrpc_methods' );

/**
 * Schließt eingehende Pings site-weit (Trackback-Spam-Surface weg).
 */
add_filter( 'pings_open', '__return_false' );

/* =========================================
   12. HEARTBEAT-DROSSEL (Frontend)
   ========================================= */

/**
 * Drosselt die Heartbeat-API im Frontend stark und
 * deaktiviert sie auf nicht-eingeloggten Sessions.
 *
 * Heartbeat ist primär für Admin-Locking & Autosave
 * relevant — im Frontend kostet sie nur Bandbreite.
 */
function hp_throttle_heartbeat(): void {
	if ( is_admin() ) {
		return;
	}

	if ( ! is_user_logged_in() ) {
		wp_deregister_script( 'heartbeat' );
		return;
	}

	// Eingeloggte Frontend-Sessions: 120 s statt 15-60 s
	wp_localize_script(
		'heartbeat',
		'heartbeatSettings',
		[ 'interval' => 120 ]
	);
}
add_action( 'wp_enqueue_scripts', 'hp_throttle_heartbeat', 100 );
