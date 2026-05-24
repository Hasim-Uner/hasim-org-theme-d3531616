<?php
/**
 * SEO Cockpit internal link graph helpers.
 *
 * @package Hasimuener_Journal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return linkable public post types for the internal graph.
 *
 * @return array<int, string>
 */
function nexus_get_seo_cockpit_linkable_post_types() {
	$types = get_post_types(
		[
			'public' => true,
		],
		'objects'
	);

	$allowed = [];

	foreach ( (array) $types as $type => $object ) {
		if ( ! ( $object instanceof WP_Post_Type ) ) {
			continue;
		}

		if ( in_array( $type, [ 'attachment', 'revision', 'nav_menu_item', 'custom_css', 'customize_changeset' ], true ) ) {
			continue;
		}

		$allowed[] = (string) $type;
	}

	return array_values( array_unique( $allowed ) );
}

/**
 * Normalize one internal link target for graph usage.
 *
 * Query strings are stripped so tracking parameters do not fragment counts.
 *
 * @param string $url Raw link target.
 * @return string
 */
function nexus_normalize_seo_cockpit_internal_link_target( $url ) {
	$normalized = nexus_normalize_seo_cockpit_url( $url );

	if ( '' === $normalized ) {
		return '';
	}

	$parts = wp_parse_url( $normalized );
	if ( ! is_array( $parts ) ) {
		return '';
	}

	$scheme = strtolower( (string) ( $parts['scheme'] ?? 'https' ) );
	$host   = strtolower( (string) ( $parts['host'] ?? '' ) );
	$path   = isset( $parts['path'] ) ? '/' . ltrim( (string) $parts['path'], '/' ) : '/';

	if ( '' === $host ) {
		return '';
	}

	if ( '/' !== $path ) {
		$path = trailingslashit( $path );
	}

	return $scheme . '://' . $host . $path;
}

/**
 * Normalize and filter one list of raw link targets to internal frontend URLs.
 *
 * @param array<int, string> $links Raw URLs.
 * @return array<int, string>
 */
function nexus_normalize_seo_cockpit_internal_link_list( $links ) {
	$home_host = strtolower( (string) wp_parse_url( home_url( '/' ), PHP_URL_HOST ) );
	$normalized = [];

	foreach ( (array) $links as $href ) {
		$href = trim( html_entity_decode( (string) $href, ENT_QUOTES, 'UTF-8' ) );

		if ( '' === $href || '#' === $href || 0 === strpos( $href, '#' ) ) {
			continue;
		}

		if ( preg_match( '#^(mailto:|tel:|javascript:)#i', $href ) ) {
			continue;
		}

		$target = nexus_normalize_seo_cockpit_internal_link_target( $href );
		if ( '' === $target ) {
			continue;
		}

		$target_host = strtolower( (string) wp_parse_url( $target, PHP_URL_HOST ) );
		if ( '' === $target_host || $target_host !== $home_host ) {
			continue;
		}

		$normalized[] = $target;
	}

	return $normalized;
}

/**
 * Extract internal links from one post content blob.
 *
 * @param string $content Post content.
 * @return array<int, string>
 */
function nexus_get_seo_cockpit_internal_links_from_content( $content ) {
	$content = (string) $content;

	if ( '' === trim( $content ) ) {
		return [];
	}

	preg_match_all( '/<a\s[^>]*href=(["\'])(.*?)\1/i', $content, $matches );

	if ( empty( $matches[2] ) || ! is_array( $matches[2] ) ) {
		return [];
	}

	return nexus_normalize_seo_cockpit_internal_link_list( array_map( 'strval', $matches[2] ) );
}

/**
 * Return a target-count map from one link list.
 *
 * @param array<int, string> $links Normalized link list.
 * @return array<string, int>
 */
function nexus_get_seo_cockpit_internal_target_counts( $links ) {
	$counts = [];

	foreach ( (array) $links as $target_url ) {
		if ( '' === $target_url ) {
			continue;
		}

		$counts[ $target_url ] = isset( $counts[ $target_url ] )
			? (int) $counts[ $target_url ] + 1
			: 1;
	}

	return $counts;
}

/**
 * Return one flat list of internal URLs from one structured link collection.
 *
 * Accepts rows that either contain a direct `url` key or a nested link map.
 *
 * @param array<int|string, mixed> $items Structured item list.
 * @return array<int, string>
 */
function nexus_get_seo_cockpit_structured_internal_urls( $items ) {
	$urls = [];

	foreach ( (array) $items as $item ) {
		if ( is_string( $item ) ) {
			$urls[] = $item;
			continue;
		}

		if ( ! is_array( $item ) ) {
			continue;
		}

		if ( ! empty( $item['url'] ) ) {
			$urls[] = (string) $item['url'];
		}
	}

	return $urls;
}

/**
 * Return canonical journal URLs used by the graph and sitewide shell model.
 *
 * @return array<string, string>
 */
function nexus_get_seo_cockpit_journal_core_urls() {
	$archive_url = static function ( $post_type, $fallback ) {
		$url = get_post_type_archive_link( (string) $post_type );
		return is_string( $url ) && '' !== $url ? $url : home_url( (string) $fallback );
	};

	return [
		'home'       => home_url( '/' ),
		'essays'     => $archive_url( 'essay', '/essays/' ),
		'notes'      => $archive_url( 'note', '/notizen/' ),
		'dossiers'   => $archive_url( 'dossier', '/dossiers/' ),
		'glossary'   => $archive_url( 'glossar', '/glossar/' ),
		'graph'      => home_url( '/wissensgraph/' ),
		'mission'    => home_url( '/mission/' ),
		'contact'    => function_exists( 'hp_get_contact_page_url' ) ? hp_get_contact_page_url() : home_url( '/kontakt/' ),
		'imprint'    => home_url( '/impressum/' ),
		'privacy'    => home_url( '/datenschutz/' ),
	];
}

/**
 * Return topic archive links assigned to one post.
 *
 * @param int $post_id Post ID.
 * @return array<int, string>
 */
function nexus_get_seo_cockpit_topic_links_for_post( $post_id ) {
	$links = [];
	$terms = get_the_terms( absint( $post_id ), 'topic' );

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return [];
	}

	foreach ( (array) $terms as $term ) {
		$link = get_term_link( $term );
		if ( is_string( $link ) ) {
			$links[] = $link;
		}
	}

	return $links;
}

/**
 * Return template-driven internal links that are not visible in raw post_content.
 *
 * @param int          $post_id Post ID.
 * @param WP_Post|null $post    Optional post object.
 * @return array<int, string>
 */
function nexus_get_seo_cockpit_template_internal_links( $post_id, $post = null ) {
	$post_id = absint( $post_id );
	$post    = $post instanceof WP_Post ? $post : get_post( $post_id );

	if ( $post_id <= 0 || ! ( $post instanceof WP_Post ) ) {
		return [];
	}

	$core      = nexus_get_seo_cockpit_journal_core_urls();
	$template  = 'page' === $post->post_type ? (string) get_page_template_slug( $post_id ) : '';
	$post_slug = sanitize_title( (string) $post->post_name );
	$links     = [];

	if ( absint( get_option( 'page_on_front' ) ) === $post_id || 'front-page.php' === $template ) {
		$links = array_merge(
			$links,
			[
				$core['essays'],
				$core['notes'],
				$core['dossiers'],
				$core['glossary'],
				$core['graph'],
				$core['mission'],
				$core['contact'],
			]
		);
	}

	if ( in_array( $post->post_type, [ 'essay', 'note', 'dossier', 'glossar', 'post' ], true ) ) {
		$links = array_merge( $links, nexus_get_seo_cockpit_topic_links_for_post( $post_id ) );
	}

	if ( in_array( $post->post_type, [ 'essay', 'post' ], true ) ) {
		$links[] = $core['essays'];
		$links[] = $core['dossiers'];
		$links[] = $core['glossary'];
	}

	if ( 'note' === $post->post_type ) {
		$links[] = $core['notes'];
		$links[] = $core['glossary'];
	}

	if ( 'dossier' === $post->post_type ) {
		$links[] = $core['dossiers'];
		$links[] = $core['graph'];

		if ( function_exists( 'hp_dossier_get_leseplan' ) ) {
			foreach ( (array) hp_dossier_get_leseplan( $post_id ) as $item ) {
				if ( $item instanceof WP_Post ) {
					$links[] = get_permalink( $item );
				}
			}
		}

		if ( function_exists( 'hp_dossier_get_begriffe' ) ) {
			foreach ( (array) hp_dossier_get_begriffe( $post_id ) as $item ) {
				if ( $item instanceof WP_Post ) {
					$links[] = get_permalink( $item );
				}
			}
		}
	}

	if ( 'glossar' === $post->post_type ) {
		$links[] = $core['glossary'];
		$links[] = $core['graph'];

		if ( function_exists( 'hp_glossar_get_dossiers' ) ) {
			foreach ( (array) hp_glossar_get_dossiers( $post_id ) as $item ) {
				if ( $item instanceof WP_Post ) {
					$links[] = get_permalink( $item );
				}
			}
		}
	}

	if ( 'page-mission.php' === $template || 'mission' === $post_slug ) {
		$links = array_merge( $links, [ $core['essays'], $core['notes'], $core['dossiers'], $core['contact'] ] );
	}

	if ( 'page-kontakt.php' === $template || in_array( $post_slug, [ 'kontakt', 'anfragen' ], true ) ) {
		$links = array_merge( $links, [ $core['mission'], $core['essays'], $core['dossiers'] ] );
	}

	if ( 'page-wissensgraph.php' === $template || 'wissensgraph' === $post_slug ) {
		$links = array_merge( $links, [ $core['glossary'], $core['dossiers'], $core['essays'] ] );
	}

	return array_values( array_unique( nexus_normalize_seo_cockpit_internal_link_list( $links ) ) );
}

/**
 * Convert one count map to a sorted list payload.
 *
 * @param array<string, int> $counts Count map.
 * @param int                $limit  Max rows.
 * @param string             $type   Source type label.
 * @param array<string, string> $labels Optional item labels keyed by URL or key.
 * @return array<int, array<string, mixed>>
 */
function nexus_get_seo_cockpit_ranked_link_list( $counts, $limit = 5, $type = 'context', $labels = [] ) {
	$counts = array_filter(
		(array) $counts,
		static function ( $count ) {
			return absint( $count ) > 0;
		}
	);

	if ( empty( $counts ) ) {
		return [];
	}

	arsort( $counts );
	$rows = [];

	foreach ( array_slice( $counts, 0, $limit, true ) as $key => $count ) {
		$rows[] = [
			'url'   => (string) $key,
			'label' => isset( $labels[ $key ] ) ? (string) $labels[ $key ] : (string) $key,
			'count' => (int) $count,
			'type'  => sanitize_key( $type ),
		];
	}

	return $rows;
}

/**
 * Return the primary site-header menu links.
 *
 * @return array<int, string>
 */
function nexus_get_seo_cockpit_primary_menu_links() {
	$links    = [];
	$location = 'hp-primary';

	if ( '' !== $location ) {
		$locations = get_nav_menu_locations();
		$menu_id   = isset( $locations[ $location ] ) ? absint( $locations[ $location ] ) : 0;
		$items     = $menu_id ? wp_get_nav_menu_items( $menu_id ) : [];

		foreach ( (array) $items as $item ) {
			if ( ! ( $item instanceof WP_Post ) && ! is_object( $item ) ) {
				continue;
			}

			$url = isset( $item->url ) ? (string) $item->url : '';

			if ( '' !== $url ) {
				$links[] = $url;
			}
		}
	}

	if ( empty( $links ) ) {
		$core  = nexus_get_seo_cockpit_journal_core_urls();
		$links = [
			$core['essays'],
			$core['notes'],
			$core['dossiers'],
			$core['glossary'],
			$core['graph'],
			$core['mission'],
		];
	}

	return nexus_normalize_seo_cockpit_internal_link_list( $links );
}

/**
 * Return the static global source definitions that the theme injects sitewide.
 *
 * @return array<string, array<string, mixed>>
 */
function nexus_get_seo_cockpit_sitewide_source_definitions() {
	static $sources = null;

	if ( is_array( $sources ) ) {
		return $sources;
	}

	$core          = nexus_get_seo_cockpit_journal_core_urls();
	$primary_links = nexus_get_seo_cockpit_primary_menu_links();
	$topic_links   = [];

	if ( function_exists( 'hp_get_curated_topics' ) ) {
		foreach ( (array) hp_get_curated_topics() as $term ) {
			$link = get_term_link( $term );
			if ( is_string( $link ) ) {
				$topic_links[] = $link;
			}
		}
	}

	$sources = [
		'site_header' => [
			'key'   => 'site_header',
			'label' => 'Masthead & Navigation',
			'links' => array_merge(
				[ $core['home'] ],
				$primary_links
			),
		],
		'editorial_archives' => [
			'key'   => 'editorial_archives',
			'label' => 'Editoriale Archive',
			'links' => [
				$core['essays'],
				$core['notes'],
				$core['dossiers'],
				$core['glossary'],
				$core['graph'],
			],
		],
		'site_footer' => [
			'key'   => 'site_footer',
			'label' => 'Kolophon',
			'links' => array_merge(
				[
					$core['mission'],
					$core['glossary'],
					$core['contact'],
					$core['imprint'],
					$core['privacy'],
				],
				$topic_links
			),
		],
	];

	foreach ( $sources as $key => $source ) {
		$normalized               = nexus_normalize_seo_cockpit_internal_link_list( (array) ( $source['links'] ?? [] ) );
		$target_counts            = nexus_get_seo_cockpit_internal_target_counts( $normalized );
		$sources[ $key ]['links'] = $normalized;
		$sources[ $key ]['link_count'] = count( $normalized );
		$sources[ $key ]['unique_targets'] = count( $target_counts );
		$sources[ $key ]['target_counts'] = $target_counts;
		$sources[ $key ]['top_targets'] = nexus_get_seo_cockpit_ranked_link_list( $target_counts, 5, 'sitewide' );
	}

	return $sources;
}

/**
 * Return sitewide shell definitions used by the theme.
 *
 * @return array<string, array<string, mixed>>
 */
function nexus_get_seo_cockpit_sitewide_shell_definitions() {
	return [
		'default'   => [
			'key'         => 'default',
			'label'       => 'Journal-Shell',
			'source_keys' => [ 'site_header', 'site_footer' ],
		],
		'editorial' => [
			'key'         => 'editorial',
			'label'       => 'Editorial-Shell',
			'source_keys' => [ 'site_header', 'editorial_archives', 'site_footer' ],
		],
		'legal'     => [
			'key'         => 'legal',
			'label'       => 'Meta-Shell',
			'source_keys' => [ 'site_header', 'site_footer' ],
		],
	];
}

/**
 * Detect the shell type for one frontend URL.
 *
 * @param string               $url     Frontend URL.
 * @param array<string, mixed> $context Optional WordPress context.
 * @return string
 */
function nexus_get_seo_cockpit_sitewide_shell_key_for_url( $url, $context = [] ) {
	$url     = nexus_normalize_seo_cockpit_internal_link_target( $url );
	$context = is_array( $context ) ? $context : [];
	$path    = '/' . ltrim( (string) wp_parse_url( $url, PHP_URL_PATH ), '/' );
	$path    = trailingslashit( $path );
	$core    = nexus_get_seo_cockpit_journal_core_urls();
	$legal_paths = [
		nexus_get_seo_cockpit_url_path( $core['imprint'] ),
		nexus_get_seo_cockpit_url_path( $core['privacy'] ),
	];

	if (
		in_array( (string) ( $context['post_type'] ?? '' ), [ 'essay', 'note', 'dossier', 'glossar', 'post' ], true )
		|| in_array( $path, [ '/essays/', '/notizen/', '/dossiers/', '/glossar/', '/wissensgraph/' ], true )
		|| 0 === strpos( $path, '/thema/' )
	) {
		return 'editorial';
	}

	if ( in_array( $path, $legal_paths, true ) ) {
		return 'legal';
	}

	return 'default';
}

/**
 * Return one sitewide outgoing payload for a concrete URL shell.
 *
 * @param string               $url     Frontend URL.
 * @param array<string, mixed> $context Optional context.
 * @return array<string, mixed>
 */
function nexus_get_seo_cockpit_sitewide_outgoing_context( $url, $context = [] ) {
	$url        = nexus_normalize_seo_cockpit_internal_link_target( $url );
	$sources    = nexus_get_seo_cockpit_sitewide_source_definitions();
	$shells     = nexus_get_seo_cockpit_sitewide_shell_definitions();
	$shell_key  = nexus_get_seo_cockpit_sitewide_shell_key_for_url( $url, $context );
	$shell      = isset( $shells[ $shell_key ] ) ? $shells[ $shell_key ] : $shells['default'];
	$target_map = [];
	$areas      = [];

	foreach ( (array) ( $shell['source_keys'] ?? [] ) as $source_key ) {
		if ( empty( $sources[ $source_key ] ) || ! is_array( $sources[ $source_key ] ) ) {
			continue;
		}

		$source = $sources[ $source_key ];
		$counts = isset( $source['target_counts'] ) && is_array( $source['target_counts'] ) ? $source['target_counts'] : [];

		foreach ( $counts as $target_url => $count ) {
			if ( $target_url === $url ) {
				continue;
			}

			$target_map[ $target_url ] = isset( $target_map[ $target_url ] )
				? (int) $target_map[ $target_url ] + (int) $count
				: (int) $count;
		}

		$areas[] = [
			'key'            => (string) ( $source['key'] ?? $source_key ),
			'label'          => (string) ( $source['label'] ?? $source_key ),
			'link_count'     => max( 0, (int) ( $source['link_count'] ?? 0 ) ),
			'unique_targets' => max( 0, (int) ( $source['unique_targets'] ?? 0 ) ),
		];
	}

	return [
		'shell'               => (string) ( $shell['key'] ?? 'default' ),
		'shell_label'         => (string) ( $shell['label'] ?? 'Standard-Shell' ),
		'outgoing_links'      => array_sum( array_map( 'intval', $target_map ) ),
		'outgoing_unique_urls' => count( $target_map ),
		'top_targets'         => nexus_get_seo_cockpit_ranked_link_list( $target_map, 5, 'sitewide' ),
		'sources'             => $areas,
	];
}

/**
 * Return the cached internal link graph.
 *
 * Context links are based on published public post content. Sitewide links are
 * counted separately from theme-injected header, blog-header and footer shells.
 *
 * @return array<string, mixed>
 */
function nexus_get_seo_cockpit_internal_link_graph() {
	$cache_key = nexus_get_seo_cockpit_cache_key( 'link_graph', [ home_url( '/' ), 'sitewide_v4' ] );
	$cached    = get_transient( $cache_key );

	if ( is_array( $cached ) ) {
		return $cached;
	}

	$post_ids = get_posts(
		[
			'post_type'              => nexus_get_seo_cockpit_linkable_post_types(),
			'post_status'            => 'publish',
			'posts_per_page'         => -1,
			'fields'                 => 'ids',
			'orderby'                => 'ID',
			'order'                  => 'ASC',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		]
	);

	$nodes = [];

	foreach ( (array) $post_ids as $post_id ) {
		$post_id    = absint( $post_id );
		$source_url = nexus_normalize_seo_cockpit_internal_link_target( get_permalink( $post_id ) );

		if ( '' === $source_url ) {
			continue;
		}

		if ( ! isset( $nodes[ $source_url ] ) ) {
			$nodes[ $source_url ] = [
				'url'                     => $source_url,
				'context_incoming_links'  => 0,
				'context_incoming_sources' => [],
				'context_outgoing_links'  => 0,
				'context_outgoing_targets' => [],
				'sitewide_incoming_links' => 0,
				'sitewide_sources'        => [],
			];
		}

		$links = nexus_get_seo_cockpit_internal_links_from_content( (string) get_post_field( 'post_content', $post_id ) );
		$links = array_merge( $links, nexus_get_seo_cockpit_template_internal_links( $post_id, get_post( $post_id ) ) );

		foreach ( $links as $target_url ) {
			if ( $source_url === $target_url ) {
				continue;
			}

			if ( ! isset( $nodes[ $target_url ] ) ) {
				$nodes[ $target_url ] = [
					'url'                     => $target_url,
					'context_incoming_links'  => 0,
					'context_incoming_sources' => [],
					'context_outgoing_links'  => 0,
					'context_outgoing_targets' => [],
					'sitewide_incoming_links' => 0,
					'sitewide_sources'        => [],
				];
			}

			$nodes[ $source_url ]['context_outgoing_links']++;
			$nodes[ $source_url ]['context_outgoing_targets'][ $target_url ] = isset( $nodes[ $source_url ]['context_outgoing_targets'][ $target_url ] )
				? $nodes[ $source_url ]['context_outgoing_targets'][ $target_url ] + 1
				: 1;

			$nodes[ $target_url ]['context_incoming_links']++;
			$nodes[ $target_url ]['context_incoming_sources'][ $source_url ] = isset( $nodes[ $target_url ]['context_incoming_sources'][ $source_url ] )
				? $nodes[ $target_url ]['context_incoming_sources'][ $source_url ] + 1
				: 1;
		}
	}

	$sitewide_sources = nexus_get_seo_cockpit_sitewide_source_definitions();

	foreach ( $sitewide_sources as $source_key => $source ) {
		$target_counts = isset( $source['target_counts'] ) && is_array( $source['target_counts'] ) ? $source['target_counts'] : [];

		foreach ( $target_counts as $target_url => $count ) {
			if ( ! isset( $nodes[ $target_url ] ) ) {
				$nodes[ $target_url ] = [
					'url'                     => $target_url,
					'context_incoming_links'  => 0,
					'context_incoming_sources' => [],
					'context_outgoing_links'  => 0,
					'context_outgoing_targets' => [],
					'sitewide_incoming_links' => 0,
					'sitewide_sources'        => [],
				];
			}

			$nodes[ $target_url ]['sitewide_incoming_links'] += (int) $count;
			$nodes[ $target_url ]['sitewide_sources'][ $source_key ] = [
				'label' => (string) ( $source['label'] ?? $source_key ),
				'count' => (int) $count,
			];
		}
	}

	foreach ( $nodes as $url => $node ) {
		$context_sources = (array) $node['context_incoming_sources'];
		$context_targets = (array) $node['context_outgoing_targets'];
		$sitewide_incoming_sources = [];

		foreach ( (array) $node['sitewide_sources'] as $source_key => $source ) {
			$sitewide_incoming_sources[ 'sitewide:' . $source_key ] = (int) ( $source['count'] ?? 0 );
		}

		arsort( $context_sources );
		arsort( $context_targets );
		arsort( $sitewide_incoming_sources );

		$sitewide_labels = [];
		foreach ( (array) $node['sitewide_sources'] as $source_key => $source ) {
			$sitewide_labels[ 'sitewide:' . $source_key ] = (string) ( $source['label'] ?? $source_key );
		}

		$nodes[ $url ] = [
			'url'                   => $url,
			'incoming_links'        => (int) $node['context_incoming_links'],
			'incoming_documents'    => count( $context_sources ),
			'outgoing_links'        => (int) $node['context_outgoing_links'],
			'outgoing_unique_urls'  => count( $context_targets ),
			'top_sources'           => nexus_get_seo_cockpit_ranked_link_list( $context_sources, 5, 'context' ),
			'top_targets'           => nexus_get_seo_cockpit_ranked_link_list( $context_targets, 5, 'context' ),
			'context'               => [
				'incoming_links'       => (int) $node['context_incoming_links'],
				'incoming_documents'   => count( $context_sources ),
				'outgoing_links'       => (int) $node['context_outgoing_links'],
				'outgoing_unique_urls' => count( $context_targets ),
				'top_sources'          => nexus_get_seo_cockpit_ranked_link_list( $context_sources, 5, 'context' ),
				'top_targets'          => nexus_get_seo_cockpit_ranked_link_list( $context_targets, 5, 'context' ),
			],
			'sitewide'              => [
				'incoming_links'     => (int) $node['sitewide_incoming_links'],
				'incoming_sources'   => count( $sitewide_incoming_sources ),
				'top_sources'        => nexus_get_seo_cockpit_ranked_link_list( $sitewide_incoming_sources, 5, 'sitewide', $sitewide_labels ),
			],
			'totals'                => [
				'incoming_links'     => (int) $node['context_incoming_links'] + (int) $node['sitewide_incoming_links'],
				'incoming_sources'   => count( $context_sources ) + count( $sitewide_incoming_sources ),
				'outgoing_links'     => (int) $node['context_outgoing_links'],
				'outgoing_unique_urls' => count( $context_targets ),
			],
		];
	}

	$graph = [
		'built_at'             => current_time( 'timestamp' ),
		'post_count'           => count( (array) $post_ids ),
		'sitewide_source_count' => count( $sitewide_sources ),
		'sitewide_sources'     => array_map(
			static function ( $source ) {
				return [
					'key'            => (string) ( $source['key'] ?? '' ),
					'label'          => (string) ( $source['label'] ?? '' ),
					'link_count'     => (int) ( $source['link_count'] ?? 0 ),
					'unique_targets' => (int) ( $source['unique_targets'] ?? 0 ),
				];
			},
			array_values( $sitewide_sources )
		),
		'nodes'                => $nodes,
		'note'                 => 'Kontextlinks stammen aus veroeffentlichten oeffentlichen Inhalten. Sitewide-Links aus Masthead, Editorial-Archiven und Kolophon werden getrennt ausgewiesen.',
	];

	set_transient( $cache_key, $graph, nexus_get_seo_cockpit_refresh_interval_seconds() );

	return $graph;
}

/**
 * Return one internal-link context payload for a URL.
 *
 * @param string               $url     Frontend URL.
 * @param array<string, mixed> $context Optional WordPress context.
 * @return array<string, mixed>
 */
function nexus_get_seo_cockpit_internal_link_context( $url, $context = [] ) {
	$url              = nexus_normalize_seo_cockpit_internal_link_target( $url );
	$graph            = nexus_get_seo_cockpit_internal_link_graph();
	$node             = isset( $graph['nodes'][ $url ] ) && is_array( $graph['nodes'][ $url ] ) ? $graph['nodes'][ $url ] : [];
	$sitewide_outgoing = nexus_get_seo_cockpit_sitewide_outgoing_context( $url, $context );
	$context_payload  = isset( $node['context'] ) && is_array( $node['context'] ) ? $node['context'] : [];
	$sitewide_payload = isset( $node['sitewide'] ) && is_array( $node['sitewide'] ) ? $node['sitewide'] : [];
	$totals_payload   = isset( $node['totals'] ) && is_array( $node['totals'] ) ? $node['totals'] : [];

	$merged_sources = array_merge(
		(array) ( $context_payload['top_sources'] ?? [] ),
		(array) ( $sitewide_payload['top_sources'] ?? [] )
	);

	usort(
		$merged_sources,
		static function ( $left, $right ) {
			return (int) ( $right['count'] ?? 0 ) <=> (int) ( $left['count'] ?? 0 );
		}
	);

	$merged_targets = array_merge(
		(array) ( $context_payload['top_targets'] ?? [] ),
		(array) ( $sitewide_outgoing['top_targets'] ?? [] )
	);

	usort(
		$merged_targets,
		static function ( $left, $right ) {
			return (int) ( $right['count'] ?? 0 ) <=> (int) ( $left['count'] ?? 0 );
		}
	);

	return [
		'status'               => 'measured',
		'incoming_links'       => (int) ( $totals_payload['incoming_links'] ?? 0 ),
		'incoming_documents'   => (int) ( $totals_payload['incoming_sources'] ?? 0 ),
		'outgoing_links'       => (int) ( $context_payload['outgoing_links'] ?? 0 ),
		'outgoing_unique_urls' => (int) ( $context_payload['outgoing_unique_urls'] ?? 0 ),
		'top_sources'          => array_slice( $merged_sources, 0, 5 ),
		'top_targets'          => array_slice( $merged_targets, 0, 5 ),
		'context'              => [
			'incoming_links'       => (int) ( $context_payload['incoming_links'] ?? 0 ),
			'incoming_documents'   => (int) ( $context_payload['incoming_documents'] ?? 0 ),
			'outgoing_links'       => (int) ( $context_payload['outgoing_links'] ?? 0 ),
			'outgoing_unique_urls' => (int) ( $context_payload['outgoing_unique_urls'] ?? 0 ),
			'top_sources'          => isset( $context_payload['top_sources'] ) && is_array( $context_payload['top_sources'] ) ? $context_payload['top_sources'] : [],
			'top_targets'          => isset( $context_payload['top_targets'] ) && is_array( $context_payload['top_targets'] ) ? $context_payload['top_targets'] : [],
		],
		'sitewide'             => [
			'incoming_links'       => (int) ( $sitewide_payload['incoming_links'] ?? 0 ),
			'incoming_sources'     => (int) ( $sitewide_payload['incoming_sources'] ?? 0 ),
			'top_sources'          => isset( $sitewide_payload['top_sources'] ) && is_array( $sitewide_payload['top_sources'] ) ? $sitewide_payload['top_sources'] : [],
			'outgoing_links'       => (int) ( $sitewide_outgoing['outgoing_links'] ?? 0 ),
			'outgoing_unique_urls' => (int) ( $sitewide_outgoing['outgoing_unique_urls'] ?? 0 ),
			'top_targets'          => isset( $sitewide_outgoing['top_targets'] ) && is_array( $sitewide_outgoing['top_targets'] ) ? $sitewide_outgoing['top_targets'] : [],
			'shell'                => (string) ( $sitewide_outgoing['shell'] ?? '' ),
			'shell_label'          => (string) ( $sitewide_outgoing['shell_label'] ?? '' ),
			'sources'              => isset( $sitewide_outgoing['sources'] ) && is_array( $sitewide_outgoing['sources'] ) ? $sitewide_outgoing['sources'] : [],
		],
		'totals'               => [
			'incoming_links'       => (int) ( $totals_payload['incoming_links'] ?? 0 ),
			'incoming_sources'     => (int) ( $totals_payload['incoming_sources'] ?? 0 ),
			'outgoing_links'       => (int) ( $context_payload['outgoing_links'] ?? 0 ) + (int) ( $sitewide_outgoing['outgoing_links'] ?? 0 ),
			'outgoing_unique_urls' => (int) ( $context_payload['outgoing_unique_urls'] ?? 0 ) + (int) ( $sitewide_outgoing['outgoing_unique_urls'] ?? 0 ),
		],
		'note'                 => (string) ( $graph['note'] ?? '' ),
	];
}

/**
 * Invalidate SEO cockpit caches when relevant content changes.
 *
 * @return void
 */
function nexus_invalidate_seo_cockpit_link_caches() {
	if ( function_exists( 'nexus_delete_seo_cockpit_snapshot_cache' ) ) {
		nexus_delete_seo_cockpit_snapshot_cache();
		return;
	}

	if ( function_exists( 'nexus_bump_seo_cockpit_cache_version' ) ) {
		nexus_bump_seo_cockpit_cache_version();
	}
}

/**
 * Invalidate the link graph when public content changes.
 *
 * @param int     $post_id Post ID.
 * @param WP_Post $post    Post object.
 * @return void
 */
function nexus_maybe_invalidate_seo_cockpit_link_caches_on_post_change( $post_id, $post ) {
	$post_id = absint( $post_id );

	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}

	if ( ! ( $post instanceof WP_Post ) ) {
		return;
	}

	if ( ! in_array( (string) $post->post_type, nexus_get_seo_cockpit_linkable_post_types(), true ) ) {
		return;
	}

	nexus_invalidate_seo_cockpit_link_caches();
}
add_action( 'save_post', 'nexus_maybe_invalidate_seo_cockpit_link_caches_on_post_change', 20, 2 );

/**
 * Invalidate the link graph when a public post is deleted or trashed.
 *
 * @param int $post_id Post ID.
 * @return void
 */
function nexus_maybe_invalidate_seo_cockpit_link_caches_on_post_delete( $post_id ) {
	$post_id = absint( $post_id );
	$post    = get_post( $post_id );

	if ( ! ( $post instanceof WP_Post ) ) {
		return;
	}

	if ( ! in_array( (string) $post->post_type, nexus_get_seo_cockpit_linkable_post_types(), true ) ) {
		return;
	}

	nexus_invalidate_seo_cockpit_link_caches();
}
add_action( 'trashed_post', 'nexus_maybe_invalidate_seo_cockpit_link_caches_on_post_delete' );
add_action( 'untrashed_post', 'nexus_maybe_invalidate_seo_cockpit_link_caches_on_post_delete' );
add_action( 'before_delete_post', 'nexus_maybe_invalidate_seo_cockpit_link_caches_on_post_delete' );

/**
 * Invalidate link caches when a navigation menu changes.
 *
 * @return void
 */
function nexus_invalidate_seo_cockpit_link_caches_on_menu_change() {
	nexus_invalidate_seo_cockpit_link_caches();
}
add_action( 'wp_update_nav_menu', 'nexus_invalidate_seo_cockpit_link_caches_on_menu_change' );
add_action( 'customize_save_after', 'nexus_invalidate_seo_cockpit_link_caches_on_menu_change' );
add_action( 'update_option_page_on_front', 'nexus_invalidate_seo_cockpit_link_caches_on_menu_change' );
add_action( 'update_option_page_for_posts', 'nexus_invalidate_seo_cockpit_link_caches_on_menu_change' );
