<?php
/**
 * Sitemap-Hygiene — Hasimuener Journal
 *
 * Bereinigt die WP-Core-Sitemap (`/wp-sitemap.xml`):
 * - Entfernt nicht genutzte Default-Taxonomien (`post_tag`, `category`)
 * - Versteckt leere `topic`-Terms (Migration-Legacy, redaktionelle Leerlinge)
 * - Schließt rechtliche Pflichtseiten aus dem Sitemap aus
 *   (Impressum, Datenschutz — sie sind über Footer erreichbar,
 *   aber kein Ranking-Ziel und kosten Crawl-Budget)
 * - Excludiert noindex-geflaggte Beiträge falls vorhanden
 *
 * Wirkt nur auf wp_sitemaps_*-Hooks (WP 5.5+), nicht auf etwaige
 * Plugin-Sitemaps.
 *
 * @package Hasimuener_Journal
 * @since   6.0.0
 */

defined( 'ABSPATH' ) || exit;

/* =========================================
   1. NICHT GENUTZTE TAXONOMIEN ENTFERNEN
   ========================================= */

/**
 * Entfernt `post_tag` und `category` aus der Sitemap-Liste.
 *
 * Das Theme nutzt ausschließlich `topic` für thematische Bündelung;
 * Default-Tags/Kategorien werden nicht redaktionell gepflegt und
 * würden nur Low-Value-URLs in den Index pumpen.
 *
 * @param array<string,WP_Taxonomy> $taxonomies
 * @return array<string,WP_Taxonomy>
 */
function hp_sitemap_filter_taxonomies( array $taxonomies ): array {
	unset( $taxonomies['post_tag'], $taxonomies['category'] );
	return $taxonomies;
}
add_filter( 'wp_sitemaps_taxonomies', 'hp_sitemap_filter_taxonomies' );

/* =========================================
   2. LEERE TOPIC-TERMS AUSBLENDEN
   ========================================= */

/**
 * Erzwingt `hide_empty=true` für Topic-Terms in der Sitemap.
 *
 * WP-Core listet standardmäßig alle Terms, auch ohne Posts.
 * Nach Migrationen (v1→v4) können Legacy-Terms mit count=0 überleben —
 * die haben keinen Search-Wert und sollen nicht im Sitemap stehen.
 *
 * @param array<string,mixed> $args
 * @param string              $taxonomy
 * @return array<string,mixed>
 */
function hp_sitemap_taxonomies_query_args( array $args, string $taxonomy ): array {
	if ( 'topic' === $taxonomy ) {
		$args['hide_empty'] = true;
	}
	return $args;
}
add_filter( 'wp_sitemaps_taxonomies_query_args', 'hp_sitemap_taxonomies_query_args', 10, 2 );

/* =========================================
   3. RECHTLICHE PFLICHTSEITEN AUSSCHLIESSEN
   ========================================= */

/**
 * Schließt Impressum, Datenschutz und andere Low-SEO-Seiten aus der
 * Page-Sitemap aus.
 *
 * @param array<string,mixed> $args
 * @param string              $post_type
 * @return array<string,mixed>
 */
function hp_sitemap_posts_query_args( array $args, string $post_type ): array {
	if ( 'page' !== $post_type ) {
		return $args;
	}

	$exclude_slugs = [ 'impressum', 'datenschutz' ];
	$exclude_ids   = [];

	foreach ( $exclude_slugs as $slug ) {
		$page = get_page_by_path( $slug );
		if ( $page instanceof WP_Post ) {
			$exclude_ids[] = (int) $page->ID;
		}
	}

	if ( $exclude_ids ) {
		$args['post__not_in'] = array_merge(
			(array) ( $args['post__not_in'] ?? [] ),
			$exclude_ids
		);
	}

	return $args;
}
add_filter( 'wp_sitemaps_posts_query_args', 'hp_sitemap_posts_query_args', 10, 2 );
