<?php
/**
 * SEO Cockpit insight rules, WordPress context and drilldown helpers.
 *
 * @package Hasimuener_Journal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return one Search Console row key by index.
 *
 * @param array<string, mixed> $row   Search Console row.
 * @param int                  $index Key index.
 * @return string
 */
function nexus_get_seo_cockpit_row_key( $row, $index = 0 ) {
	return isset( $row['keys'][ $index ] ) ? (string) $row['keys'][ $index ] : '';
}

/**
 * Return one table row cell value from a Search Console row.
 *
 * @param array<string, mixed> $row Search Console row.
 * @return string
 */
function nexus_get_seo_cockpit_row_label( $row ) {
	return nexus_get_seo_cockpit_row_key( $row, 0 );
}

/**
 * Resolve one known legacy redirect target for a frontend URL.
 *
 * @param string $url Frontend URL.
 * @return string
 */
function nexus_get_seo_cockpit_redirect_target_for_url( $url ) {
	if ( ! function_exists( 'hp_get_legacy_topic_redirect_map' ) ) {
		return '';
	}

	$path = wp_parse_url( $url, PHP_URL_PATH );
	$path = trailingslashit( '/' . ltrim( (string) $path, '/' ) );
	$map  = hp_get_legacy_topic_redirect_map();

	if ( ! preg_match( '#^/thema/([^/]+)/$#', $path, $matches ) ) {
		return '';
	}

	$slug = sanitize_title( (string) ( $matches[1] ?? '' ) );
	if ( empty( $map[ $slug ] ) ) {
		return '';
	}

	return home_url( '/thema/' . sanitize_title( (string) $map[ $slug ] ) . '/' );
}

/**
 * Return the canonical analysis URL used for scoring and query grouping.
 *
 * Search Console keeps historical rows for redirected URLs for a while after a
 * route consolidation. For prioritization those rows should strengthen the
 * canonical target, not create false cannibalization tasks.
 *
 * @param string $url Frontend URL.
 * @return string
 */
function nexus_get_seo_cockpit_effective_insight_url( $url ) {
	$url     = nexus_normalize_seo_cockpit_url( $url );
	$context = nexus_get_seo_cockpit_wp_context_for_url( $url );

	if ( 'legacy_redirect' === (string) ( $context['page_type'] ?? '' ) && ! empty( $context['canonical'] ) ) {
		return nexus_normalize_seo_cockpit_url( (string) $context['canonical'] );
	}

	return $url;
}

/**
 * Decide whether a query should be ignored by journal-priority rules.
 *
 * @param string $query Search query.
 * @return bool
 */
function nexus_is_seo_cockpit_non_target_query( $query ) {
	$normalized = nexus_normalize_seo_cockpit_query( $query );

	if ( '' === $normalized ) {
		return true;
	}

	return (bool) apply_filters( 'nexus_seo_cockpit_non_target_query', false, $query, $normalized );
}

/**
 * Return a default internal-link payload.
 *
 * @param string $status Status key.
 * @param string $note   Human note.
 * @return array<string, mixed>
 */
function nexus_get_seo_cockpit_default_internal_links( $status = 'pending', $note = '' ) {
	return [
		'status'               => sanitize_key( (string) $status ),
		'incoming_links'       => 0,
		'incoming_documents'   => 0,
		'outgoing_links'       => 0,
		'outgoing_unique_urls' => 0,
		'top_sources'          => [],
		'top_targets'          => [],
		'context'              => [
			'incoming_links'       => 0,
			'incoming_documents'   => 0,
			'outgoing_links'       => 0,
			'outgoing_unique_urls' => 0,
			'top_sources'          => [],
			'top_targets'          => [],
		],
		'sitewide'             => [
			'incoming_links'       => 0,
			'incoming_sources'     => 0,
			'top_sources'          => [],
			'outgoing_links'       => 0,
			'outgoing_unique_urls' => 0,
			'top_targets'          => [],
			'shell'                => '',
			'shell_label'          => '',
			'sources'              => [],
		],
		'totals'               => [
			'incoming_links'       => 0,
			'incoming_sources'     => 0,
			'outgoing_links'       => 0,
			'outgoing_unique_urls' => 0,
		],
		'note'                 => '' !== $note ? $note : 'Interne Link-Zählung ist noch nicht verfügbar.',
	];
}

/**
 * Resolve one frontend URL to a local WordPress object where possible.
 *
 * @param string $url Frontend URL.
 * @return array<string, mixed>
 */
function nexus_get_seo_cockpit_wp_context_for_url( $url ) {
	static $cache = [];

	$url = nexus_normalize_seo_cockpit_url( $url );

	if ( isset( $cache[ $url ] ) ) {
		return $cache[ $url ];
	}

	$front_page_id = absint( get_option( 'page_on_front' ) );
	$posts_page_id = absint( get_option( 'page_for_posts' ) );
	$resolved_id   = url_to_postid( $url );
	$redirect_url  = nexus_get_seo_cockpit_redirect_target_for_url( $url );

	if ( 0 === $resolved_id && home_url( '/' ) === $url ) {
		$resolved_id = $front_page_id;
	}

	if ( 0 === $resolved_id && $posts_page_id && nexus_normalize_seo_cockpit_url( get_permalink( $posts_page_id ) ) === $url ) {
		$resolved_id = $posts_page_id;
	}

	$context = [
		'resolved'               => false,
		'url'                    => $url,
		'post_id'                => 0,
		'post_title'             => '',
		'post_type'              => '',
		'post_status'            => '',
		'page_type'              => '',
		'template'               => '',
		'modified_at'            => 0,
		'seo_title'              => '',
		'seo_description'        => '',
		'seo_title_present'      => false,
		'seo_description_present' => false,
		'title_source'           => '',
		'description_source'     => '',
		'canonical'              => '',
		'canonical_present'      => false,
		'noindex'                => false,
		'in_sitemap'             => false,
		'word_count'             => 0,
		'internal_links'         => nexus_get_seo_cockpit_default_internal_links( 'pending', 'Interne Link-Zählung ist für eine spätere Stufe vorbereitet.' ),
		'edit_link'              => '',
		'frontend_link'          => $url,
		'snippet_issues'         => [],
	];

	$core_urls = function_exists( 'nexus_get_seo_cockpit_journal_core_urls' ) ? nexus_get_seo_cockpit_journal_core_urls() : [];
	$path      = nexus_get_seo_cockpit_url_path( $url );
	$archive_contexts = [
		'essays'   => [ 'label' => 'Essays', 'role' => 'essay_archive' ],
		'notes'    => [ 'label' => 'Notizen', 'role' => 'note_archive' ],
		'dossiers' => [ 'label' => 'Dossiers', 'role' => 'dossier_archive' ],
		'glossary' => [ 'label' => 'Glossar', 'role' => 'glossary_archive' ],
	];

	foreach ( $archive_contexts as $key => $archive ) {
		$archive_url = isset( $core_urls[ $key ] ) ? nexus_normalize_seo_cockpit_url( (string) $core_urls[ $key ] ) : '';
		if ( '' === $archive_url || $archive_url !== $url ) {
			continue;
		}

		$context = array_merge(
			$context,
			[
				'resolved'                => true,
				'post_title'              => (string) $archive['label'],
				'page_type'               => (string) $archive['role'],
				'seo_title'               => (string) $archive['label'] . ' - ' . get_bloginfo( 'name' ),
				'seo_description'         => function_exists( 'hp_get_meta_description' ) ? hp_get_meta_description() : '',
				'seo_title_present'       => true,
				'seo_description_present' => true,
				'title_source'            => 'archive',
				'description_source'      => 'archive',
				'canonical'               => $archive_url,
				'canonical_present'       => true,
				'in_sitemap'              => true,
				'frontend_link'           => $archive_url,
			]
		);

		break;
	}

	if ( ! $context['resolved'] && 0 === strpos( $path, '/thema/' ) ) {
		$slug = sanitize_title( basename( trim( $path, '/' ) ) );
		$term = '' !== $slug ? get_term_by( 'slug', $slug, 'topic' ) : false;

		if ( $term instanceof WP_Term ) {
			$term_link = get_term_link( $term );
			$term_link = is_string( $term_link ) ? nexus_normalize_seo_cockpit_url( $term_link ) : $url;
			$term_desc = wp_strip_all_tags( (string) term_description( $term, 'topic' ) );

			$context = array_merge(
				$context,
				[
					'resolved'                => true,
					'post_title'              => $term->name,
					'page_type'               => 'topic_archive',
					'seo_title'               => 'Thema: ' . $term->name,
					'seo_description'         => '' !== $term_desc ? $term_desc : 'Thematische Sammlung im Journal von Haşim Üner.',
					'seo_title_present'       => true,
					'seo_description_present' => true,
					'title_source'            => 'taxonomy',
					'description_source'      => 'taxonomy',
					'canonical'               => $term_link,
					'canonical_present'       => true,
					'in_sitemap'              => true,
					'frontend_link'           => $term_link,
				]
			);
		}
	}

	if ( '' !== $redirect_url ) {
		$context = [
			'resolved'                => true,
			'url'                     => $url,
			'post_id'                 => 0,
			'post_title'              => 'Legacy Redirect',
			'post_type'               => '',
			'post_status'             => 'redirect',
			'page_type'               => 'legacy_redirect',
			'template'                => '',
			'modified_at'             => 0,
			'seo_title'               => '',
			'seo_description'         => '',
			'seo_title_present'       => false,
			'seo_description_present' => false,
			'title_source'            => '',
			'description_source'      => '',
			'canonical'               => $redirect_url,
			'canonical_present'       => true,
			'noindex'                 => false,
			'in_sitemap'              => false,
			'word_count'              => 0,
			'internal_links'          => nexus_get_seo_cockpit_default_internal_links( 'n/a', 'Diese URL wird serverseitig auf ein kanonisches Ziel weitergeleitet.' ),
			'edit_link'               => '',
			'frontend_link'           => $redirect_url,
			'snippet_issues'          => [],
		];

		$cache[ $url ] = $context;

		return $context;
	}

	if ( $resolved_id ) {
		$post = get_post( $resolved_id );

		if ( $post instanceof WP_Post ) {
			$seo_context = nexus_get_seo_cockpit_post_seo_context( $resolved_id );
			$template    = 'page' === $post->post_type ? ( get_page_template_slug( $resolved_id ) ?: 'default' ) : $post->post_type;
			$page_type   = $post->post_type;

			if ( $front_page_id === $resolved_id ) {
				$page_type = 'front_page';
			} elseif ( $posts_page_id === $resolved_id ) {
				$page_type = 'blog_index';
			}

			$context = [
				'resolved'                => true,
				'url'                     => $url,
				'post_id'                 => $resolved_id,
				'post_title'              => get_the_title( $resolved_id ),
				'post_type'               => (string) $post->post_type,
				'post_status'             => (string) $post->post_status,
				'page_type'               => $page_type,
				'template'                => (string) $template,
				'modified_at'             => (int) get_post_modified_time( 'U', true, $resolved_id ),
				'seo_title'               => (string) ( $seo_context['title'] ?? '' ),
				'seo_description'         => (string) ( $seo_context['description'] ?? '' ),
				'seo_title_present'       => '' !== (string) ( $seo_context['title'] ?? '' ),
				'seo_description_present' => '' !== (string) ( $seo_context['description'] ?? '' ),
				'title_source'            => (string) ( $seo_context['title_source'] ?? '' ),
				'description_source'      => (string) ( $seo_context['description_source'] ?? '' ),
				'canonical'               => (string) ( $seo_context['canonical'] ?? '' ),
				'canonical_present'       => '' !== (string) ( $seo_context['canonical'] ?? '' ),
				'noindex'                 => ! empty( $seo_context['noindex'] ),
				'in_sitemap'              => nexus_is_seo_cockpit_post_in_sitemap( $post, ! empty( $seo_context['noindex'] ) ),
				'word_count'              => nexus_get_seo_cockpit_post_word_count( $resolved_id ),
				'internal_links'          => nexus_get_seo_cockpit_default_internal_links( 'pending', 'Interne Link-Zählung ist für eine spätere Stufe vorbereitet.' ),
				'edit_link'               => (string) get_edit_post_link( $resolved_id, 'raw' ),
				'frontend_link'           => (string) get_permalink( $resolved_id ),
				'snippet_issues'          => nexus_get_seo_cockpit_snippet_issues( $seo_context ),
			];
		}
	}

	if ( function_exists( 'nexus_get_seo_cockpit_internal_link_context' ) ) {
		$context['internal_links'] = nexus_get_seo_cockpit_internal_link_context(
			(string) ( $context['frontend_link'] ?: $url ),
			$context
		);
	}

	$cache[ $url ] = $context;

	return $context;
}

/**
 * Build a WordPress context map for one page report.
 *
 * @param array<int, array<string, mixed>> $rows Page rows.
 * @return array<string, array<string, mixed>>
 */
function nexus_get_seo_cockpit_page_context_map( $rows ) {
	$urls = [];

	foreach ( (array) $rows as $row ) {
		$url = nexus_normalize_seo_cockpit_url( nexus_get_seo_cockpit_row_key( $row, 0 ) );
		if ( '' !== $url ) {
			$urls[ $url ] = $url;
		}
	}

	$contexts = [];
	foreach ( $urls as $url ) {
		$contexts[ $url ] = nexus_get_seo_cockpit_wp_context_for_url( $url );
	}

	return $contexts;
}

/**
 * Return the effective SEO context for one post.
 *
 * @param int $post_id Post ID.
 * @return array<string, mixed>
 */
function nexus_get_seo_cockpit_post_seo_context( $post_id ) {
	$post_id = absint( $post_id );

	$post = get_post( $post_id );
	if ( ! ( $post instanceof WP_Post ) ) {
		return [];
	}

	$title          = get_the_title( $post_id );
	$title_source   = 'document_title';
	$stored_desc    = (string) get_post_meta( $post_id, '_hp_meta_description', true );
	$description    = trim( $stored_desc );
	$desc_source    = '' !== $description ? 'stored' : 'fallback';

	if ( '' === $description && has_excerpt( $post_id ) ) {
		$description = wp_strip_all_tags( get_the_excerpt( $post ) );
		$desc_source = 'excerpt';
	}

	if ( '' === $description ) {
		$description = wp_trim_words( wp_strip_all_tags( (string) $post->post_content ), 25, '…' );
		$desc_source = 'content';
	}

	$noindex = false;

	return [
		'title'              => trim( wp_strip_all_tags( (string) $title ) ),
		'description'        => trim( wp_strip_all_tags( (string) $description ) ),
		'canonical'          => (string) get_permalink( $post_id ),
		'robots'             => $noindex ? 'noindex, nofollow' : 'index, follow',
		'noindex'            => $noindex,
		'title_source'       => $title_source,
		'description_source' => $desc_source,
	];
}

/**
 * Return estimated word count for one post.
 *
 * @param int $post_id Post ID.
 * @return int
 */
function nexus_get_seo_cockpit_post_word_count( $post_id ) {
	$content = (string) get_post_field( 'post_content', $post_id );
	$text    = wp_strip_all_tags( strip_shortcodes( $content ) );

	if ( '' === $text ) {
		return 0;
	}

	preg_match_all( '/[\p{L}\p{N}\']+/u', $text, $matches );

	return ! empty( $matches[0] ) ? count( $matches[0] ) : 0;
}

/**
 * Determine whether a post should be expected in the native sitemap layer.
 *
 * @param WP_Post $post    Post object.
 * @param bool    $noindex Whether the page is noindex.
 * @return bool
 */
function nexus_is_seo_cockpit_post_in_sitemap( $post, $noindex = false ) {
	if ( ! ( $post instanceof WP_Post ) ) {
		return false;
	}

	$type_object = get_post_type_object( $post->post_type );

	if ( 'publish' !== $post->post_status || ! $type_object || ! $type_object->public || $noindex ) {
		return false;
	}

	if ( function_exists( 'wp_sitemaps_get_server' ) ) {
		return true;
	}

	return false;
}

/**
 * Return snippet weakness flags for one SEO context.
 *
 * @param array<string, mixed> $seo_context SEO context.
 * @return array<int, string>
 */
function nexus_get_seo_cockpit_snippet_issues( $seo_context ) {
	$title       = trim( (string) ( $seo_context['title'] ?? '' ) );
	$description = trim( (string) ( $seo_context['description'] ?? '' ) );
	$issues      = [];

	$title_length = mb_strlen( $title );
	$desc_length  = mb_strlen( $description );

	if ( '' === $title ) {
		$issues[] = 'title_missing';
	} elseif ( $title_length < 35 ) {
		$issues[] = 'title_short';
	} elseif ( $title_length > 60 ) {
		$issues[] = 'title_long';
	}

	if ( '' === $description ) {
		$issues[] = 'description_missing';
	} elseif ( $desc_length < 90 ) {
		$issues[] = 'description_short';
	} elseif ( $desc_length > 160 ) {
		$issues[] = 'description_long';
	}

	return $issues;
}

/**
 * Return one sortable severity score.
 *
 * @param string $severity Severity label.
 * @return int
 */
function nexus_get_seo_cockpit_severity_score( $severity ) {
	$map = [
		'critical' => 400,
		'high'     => 300,
		'medium'   => 200,
		'low'      => 100,
	];

	return $map[ sanitize_key( (string) $severity ) ] ?? 0;
}

/**
 * Return one normalized path for cockpit role mapping.
 *
 * @param string $url Frontend URL.
 * @return string
 */
function nexus_get_seo_cockpit_url_path( $url ) {
	$url = nexus_normalize_seo_cockpit_url( $url );

	if ( '' === $url ) {
		return '/';
	}

	$path = (string) wp_parse_url( $url, PHP_URL_PATH );
	$path = '/' . ltrim( $path, '/' );

	return '/' === $path ? '/' : trailingslashit( $path );
}

/**
 * Return the semantic page role for one cockpit URL context.
 *
 * @param array<string, mixed> $context Optional WordPress context.
 * @param string               $url     Optional frontend URL.
 * @return string
 */
function nexus_get_seo_cockpit_page_role( $context = [], $url = '' ) {
	$context   = is_array( $context ) ? $context : [];
	$url       = '' !== $url ? $url : (string) ( $context['frontend_link'] ?? $context['url'] ?? '' );
	$path      = nexus_get_seo_cockpit_url_path( $url );
	$page_type = (string) ( $context['page_type'] ?? '' );
	$post_type = (string) ( $context['post_type'] ?? '' );
	$core      = function_exists( 'nexus_get_seo_cockpit_journal_core_urls' ) ? nexus_get_seo_cockpit_journal_core_urls() : [];
	$paths     = [];

	foreach ( $core as $key => $mapped_url ) {
		$paths[ $key ] = nexus_get_seo_cockpit_url_path( (string) $mapped_url );
	}

	if ( 'legacy_redirect' === $page_type ) {
		return 'legacy';
	}

	if ( 'front_page' === $page_type || '/' === $path ) {
		return 'home';
	}

	if ( ! empty( $paths['contact'] ) && $paths['contact'] === $path ) {
		return 'contact';
	}

	if ( ! empty( $paths['mission'] ) && $paths['mission'] === $path ) {
		return 'mission';
	}

	if ( ! empty( $paths['graph'] ) && $paths['graph'] === $path ) {
		return 'graph';
	}

	if ( in_array( $path, array_filter( [ $paths['essays'] ?? '', $paths['notes'] ?? '', $paths['dossiers'] ?? '', $paths['glossary'] ?? '' ] ), true ) ) {
		return 'hub';
	}

	if ( 'topic_archive' === $page_type || 0 === strpos( $path, '/thema/' ) ) {
		return 'topic';
	}

	if ( in_array( $path, array_filter( [ $paths['imprint'] ?? '', $paths['privacy'] ?? '' ] ), true ) ) {
		return 'legal';
	}

	if ( in_array( $post_type, [ 'essay', 'note', 'dossier', 'glossar' ], true ) ) {
		return $post_type;
	}

	if ( 'post' === $post_type ) {
		return 'essay';
	}

	if ( 'blog_index' === $page_type || 0 === strpos( $path, '/category/' ) || 0 === strpos( $path, '/tag/' ) || 0 === strpos( $path, '/author/' ) ) {
		return 'hub';
	}

	if ( ! empty( $context['noindex'] ) ) {
		return 'utility';
	}

	if ( 'page' === $post_type ) {
		return 'page';
	}

	return 'unknown';
}

/**
 * Return one human label for a cockpit page role.
 *
 * @param string $role Page role.
 * @return string
 */
function nexus_get_seo_cockpit_page_role_label( $role ) {
	$labels = [
		'home'    => 'Startseite',
		'essay'   => 'Essay',
		'note'    => 'Notiz',
		'dossier' => 'Dossier',
		'glossar' => 'Glossar',
		'hub'     => 'Archiv',
		'topic'   => 'Thema',
		'graph'   => 'Wissensgraph',
		'mission' => 'Mission',
		'contact' => 'Kontakt',
		'legal'   => 'Rechtlich',
		'utility' => 'Utility',
		'page'    => 'Seite',
		'legacy'  => 'Legacy',
		'unknown' => 'Sonstiges',
	];

	return $labels[ sanitize_key( (string) $role ) ] ?? 'Sonstiges';
}

/**
 * Determine whether one page role is business-critical.
 *
 * @param string $role Page role.
 * @return bool
 */
function nexus_is_seo_cockpit_high_value_role( $role ) {
	return in_array( sanitize_key( (string) $role ), [ 'home', 'essay', 'note', 'dossier', 'glossar', 'hub', 'topic', 'graph', 'mission' ], true );
}

/**
 * Return business-value and funnel-proximity scores for a page role.
 *
 * @param string $role Page role.
 * @return array<string, int>
 */
function nexus_get_seo_cockpit_page_role_scores( $role ) {
	$map = [
		'home'    => [ 'business' => 18, 'funnel' => 10 ],
		'essay'   => [ 'business' => 20, 'funnel' => 12 ],
		'dossier' => [ 'business' => 19, 'funnel' => 12 ],
		'glossar' => [ 'business' => 17, 'funnel' => 10 ],
		'graph'   => [ 'business' => 16, 'funnel' => 9 ],
		'topic'   => [ 'business' => 15, 'funnel' => 9 ],
		'hub'     => [ 'business' => 14, 'funnel' => 8 ],
		'note'    => [ 'business' => 12, 'funnel' => 7 ],
		'mission' => [ 'business' => 10, 'funnel' => 6 ],
		'contact' => [ 'business' => 7, 'funnel' => 5 ],
		'page'    => [ 'business' => 6, 'funnel' => 4 ],
		'utility' => [ 'business' => 2, 'funnel' => 1 ],
		'legal'   => [ 'business' => 0, 'funnel' => 0 ],
		'legacy'  => [ 'business' => 1, 'funnel' => 0 ],
		'unknown' => [ 'business' => 5, 'funnel' => 3 ],
	];

	return $map[ sanitize_key( (string) $role ) ] ?? $map['unknown'];
}

/**
 * Return one priority label from a priority bucket.
 *
 * @param string $bucket Priority bucket.
 * @return string
 */
function nexus_get_seo_cockpit_priority_label( $bucket ) {
	$labels = [
		'critical' => 'P1',
		'high'     => 'P2',
		'medium'   => 'P3',
		'low'      => 'P4',
	];

	return $labels[ sanitize_key( (string) $bucket ) ] ?? 'P4';
}

/**
 * Return one actionability score for an insight type.
 *
 * @param string $type Insight type.
 * @return int
 */
function nexus_get_seo_cockpit_actionability_score( $type ) {
	$map = [
		'INDEXING_MISMATCH'         => 14,
		'QUICK_WIN'                 => 12,
		'CTR_OPPORTUNITY'           => 11,
		'MONEY_PAGE_UNDERPERFORMING' => 12,
		'ORPHAN_VALUE_PAGE'         => 10,
		'WEAK_FUNNEL_BRIDGE'        => 10,
		'SNIPPET_WEAKNESS'          => 9,
		'DECAY'                     => 8,
		'POSSIBLE_CANNIBALIZATION'  => 8,
		'LOW_SIGNAL'                => 6,
	];

	return $map[ strtoupper( (string) $type ) ] ?? 6;
}

/**
 * Return one demand score from impression volume.
 *
 * @param float $impressions Impression count.
 * @return int
 */
function nexus_get_seo_cockpit_demand_score( $impressions ) {
	$impressions = (float) $impressions;

	if ( $impressions >= 500 ) {
		return 20;
	}

	if ( $impressions >= 250 ) {
		return 17;
	}

	if ( $impressions >= 120 ) {
		return 14;
	}

	if ( $impressions >= 60 ) {
		return 10;
	}

	if ( $impressions >= 20 ) {
		return 6;
	}

	return 2;
}

/**
 * Return one confidence score for an insight.
 *
 * @param float                $impressions Impressions.
 * @param float                $clicks      Clicks.
 * @param array<string, mixed> $context     WordPress context.
 * @param array<string, mixed> $koko_page   Koko page payload.
 * @return int
 */
function nexus_get_seo_cockpit_confidence_score( $impressions, $clicks, $context = [], $koko_page = [] ) {
	$score       = 0;
	$impressions = (float) $impressions;
	$clicks      = (float) $clicks;
	$context     = is_array( $context ) ? $context : [];
	$koko_page   = is_array( $koko_page ) ? $koko_page : [];

	if ( ! empty( $context['resolved'] ) ) {
		$score += 3;
	}

	if ( $impressions >= 80 ) {
		$score += 3;
	} elseif ( $impressions >= 20 ) {
		$score += 1;
	}

	if ( $clicks >= 10 ) {
		$score += 2;
	} elseif ( $clicks > 0 ) {
		$score += 1;
	}

	if ( ! empty( $koko_page ) ) {
		$score += 2;
	}

	return min( 10, $score );
}

/**
 * Return one page-level lead payload from the snapshot.
 *
 * @param array<string, mixed> $snapshot Snapshot payload.
 * @param string               $url      Frontend URL.
 * @return array<string, mixed>
 */
function nexus_get_seo_cockpit_lead_page_for_url( $snapshot, $url ) {
	$url = nexus_get_seo_cockpit_internal_attribution_url( $url );

	if ( '' === $url ) {
		return [];
	}

	return isset( $snapshot['leads']['page_map'][ $url ] ) && is_array( $snapshot['leads']['page_map'][ $url ] )
		? $snapshot['leads']['page_map'][ $url ]
		: [];
}

/**
 * Return one priority bucket from a numeric score.
 *
 * @param int $score Priority score.
 * @return string
 */
function nexus_get_seo_cockpit_priority_bucket( $score ) {
	$score = absint( $score );

	if ( $score >= 75 ) {
		return 'critical';
	}

	if ( $score >= 58 ) {
		return 'high';
	}

	if ( $score >= 40 ) {
		return 'medium';
	}

	return 'low';
}

/**
 * Return one current page row by URL from the snapshot.
 *
 * @param array<string, mixed> $snapshot Snapshot payload.
 * @param string               $url      Frontend URL.
 * @return array<string, mixed>
 */
function nexus_get_seo_cockpit_current_page_row_for_url( $snapshot, $url ) {
	$url = nexus_normalize_seo_cockpit_url( $url );

	foreach ( (array) ( $snapshot['current_page_rows'] ?? [] ) as $row ) {
		if ( $url === nexus_normalize_seo_cockpit_url( nexus_get_seo_cockpit_row_key( $row, 0 ) ) ) {
			return is_array( $row ) ? $row : [];
		}
	}

	return [];
}

/**
 * Enrich one insight with business-aware priority data.
 *
 * @param array<string, mixed> $insight  Insight payload.
 * @param array<string, mixed> $snapshot Snapshot payload.
 * @return array<string, mixed>
 */
function nexus_enrich_seo_cockpit_insight_priority( $insight, $snapshot ) {
	$url          = nexus_normalize_seo_cockpit_url( (string) ( $insight['url'] ?? '' ) );
	$page_context = isset( $snapshot['page_contexts'][ $url ] ) && is_array( $snapshot['page_contexts'][ $url ] ) ? $snapshot['page_contexts'][ $url ] : [];
	$page_row     = nexus_get_seo_cockpit_current_page_row_for_url( $snapshot, $url );
	$koko_page    = isset( $snapshot['koko']['page_map'][ $url ] ) && is_array( $snapshot['koko']['page_map'][ $url ] ) ? $snapshot['koko']['page_map'][ $url ] : [];
	$lead_page    = nexus_get_seo_cockpit_lead_page_for_url( $snapshot, $url );
	$page_role    = nexus_get_seo_cockpit_page_role( $page_context, $url );
	$role_scores  = nexus_get_seo_cockpit_page_role_scores( $page_role );
	$type         = strtoupper( (string) ( $insight['type'] ?? '' ) );
	$severity     = sanitize_key( (string) ( $insight['severity'] ?? 'low' ) );
	$impressions  = (float) ( $insight['metrics']['impressions'] ?? $insight['metrics']['total_impressions'] ?? $page_row['impressions'] ?? 0 );
	$clicks       = (float) ( $insight['metrics']['clicks'] ?? $insight['metrics']['current_clicks'] ?? $page_row['clicks'] ?? 0 );
	$severity_map = [
		'critical' => 24,
		'high'     => 18,
		'medium'   => 12,
		'low'      => 6,
	];
	$components   = [
		'severity'      => $severity_map[ $severity ] ?? 6,
		'demand'        => nexus_get_seo_cockpit_demand_score( $impressions ),
		'business'      => (int) ( $role_scores['business'] ?? 0 ),
		'funnel'        => (int) ( $role_scores['funnel'] ?? 0 ),
		'actionability' => nexus_get_seo_cockpit_actionability_score( $type ),
		'lead_signal'   => function_exists( 'nexus_get_seo_cockpit_lead_signal_score' ) ? nexus_get_seo_cockpit_lead_signal_score( $lead_page ) : 0,
		'confidence'    => nexus_get_seo_cockpit_confidence_score( $impressions, $clicks, $page_context, $koko_page ),
	];
	$score        = min( 100, array_sum( $components ) );
	$bucket       = nexus_get_seo_cockpit_priority_bucket( $score );

	return array_merge(
		$insight,
		[
			'page_role'        => $page_role,
			'page_role_label'  => nexus_get_seo_cockpit_page_role_label( $page_role ),
			'priority_score'   => $score,
			'priority_bucket'  => $bucket,
			'priority_label'   => nexus_get_seo_cockpit_priority_label( $bucket ),
			'priority_parts'   => $components,
			'koko_visitors'    => (float) ( $koko_page['visitors'] ?? 0 ),
			'koko_pageviews'   => (float) ( $koko_page['pageviews'] ?? 0 ),
			'lead_requests_current' => (int) ( $lead_page['current']['requests'] ?? 0 ),
			'lead_requests_lifetime' => (int) ( $lead_page['lifetime']['requests'] ?? 0 ),
			'lead_progressed_lifetime' => (int) ( $lead_page['lifetime']['progressed'] ?? 0 ),
		]
	);
}

/**
 * Create a normalized insight payload.
 *
 * @param array<string, mixed> $insight Raw insight data.
 * @return array<string, mixed>
 */
function nexus_build_seo_cockpit_insight( $insight ) {
	return [
		'type'               => strtoupper( str_replace( '-', '_', sanitize_key( (string) ( $insight['type'] ?? 'unknown' ) ) ) ),
		'severity'           => sanitize_key( (string) ( $insight['severity'] ?? 'low' ) ),
		'label'              => trim( wp_strip_all_tags( (string) ( $insight['label'] ?? '' ) ) ),
		'reason'             => trim( wp_strip_all_tags( (string) ( $insight['reason'] ?? '' ) ) ),
		'url'                => nexus_normalize_seo_cockpit_url( (string) ( $insight['url'] ?? '' ) ),
		'query'              => trim( wp_strip_all_tags( (string) ( $insight['query'] ?? '' ) ) ),
		'metrics'            => is_array( $insight['metrics'] ?? null ) ? $insight['metrics'] : [],
		'recommended_action' => trim( wp_strip_all_tags( (string) ( $insight['recommended_action'] ?? '' ) ) ),
	];
}

/**
 * Build the full insight list for one snapshot.
 *
 * @param array<string, mixed> $snapshot Snapshot payload.
 * @return array<int, array<string, mixed>>
 */
function nexus_get_seo_cockpit_insights( $snapshot ) {
	$insights     = [];
	$overall_ctr  = (float) ( $snapshot['overview']['current']['ctr'] ?? 0 );
	$page_context = isset( $snapshot['page_contexts'] ) && is_array( $snapshot['page_contexts'] ) ? $snapshot['page_contexts'] : [];
	$seen         = [];

	foreach ( (array) ( $snapshot['query_page_rows'] ?? [] ) as $row ) {
		$raw_url     = nexus_normalize_seo_cockpit_url( nexus_get_seo_cockpit_row_key( $row, 0 ) );
		$url         = nexus_get_seo_cockpit_effective_insight_url( $raw_url );
		$query       = nexus_get_seo_cockpit_row_key( $row, 1 );
		$impressions = (float) ( $row['impressions'] ?? 0 );
		$ctr         = (float) ( $row['ctr'] ?? 0 );
		$position    = (float) ( $row['position'] ?? 0 );

		if ( '' === $url || '' === $query ) {
			continue;
		}

		if ( nexus_is_seo_cockpit_non_target_query( $query ) ) {
			continue;
		}

		if ( $position >= 8 && $position <= 20 && $impressions >= 25 ) {
			$insights[] = nexus_build_seo_cockpit_insight(
				[
					'type'               => 'QUICK_WIN',
					'severity'           => $position <= 12 && $impressions >= 80 ? 'high' : 'medium',
					'label'              => sprintf( 'Quick Win für "%s"', $query ),
					'reason'             => sprintf( 'Die URL rankt bereits auf Position %.1f bei %.0f Impressionen.', $position, $impressions ),
					'url'                => $url,
					'query'              => $query,
					'metrics'            => [
						'position'    => $position,
						'impressions' => $impressions,
						'ctr'         => $ctr,
					],
					'recommended_action' => 'Title, Description und interne Links dieser Seite zuerst nachschärfen.',
				]
			);
		}

		if ( $position <= 12 && $impressions >= 120 && $ctr < max( 0.01, $overall_ctr * 0.65 ) ) {
			$insights[] = nexus_build_seo_cockpit_insight(
				[
					'type'               => 'CTR_OPPORTUNITY',
					'severity'           => $impressions >= 300 ? 'high' : 'medium',
					'label'              => sprintf( 'CTR-Chance für "%s"', $query ),
					'reason'             => sprintf( 'Viele Impressionen (%.0f), aber nur %.1f%% CTR bei Position %.1f.', $impressions, $ctr * 100, $position ),
					'url'                => $url,
					'query'              => $query,
					'metrics'            => [
						'position'    => $position,
						'impressions' => $impressions,
						'ctr'         => $ctr,
						'baseline_ctr' => $overall_ctr,
					],
					'recommended_action' => 'Snippet gezielt auf Suchintention, Begriffsklarheit und redaktionellen Nutzen ausrichten.',
				]
			);
		}

		if ( $position > 20 && $position <= 40 && $impressions >= 15 ) {
			$insights[] = nexus_build_seo_cockpit_insight(
				[
					'type'               => 'LOW_SIGNAL',
					'severity'           => $impressions >= 60 ? 'medium' : 'low',
					'label'              => sprintf( 'Schwaches Signal für "%s"', $query ),
					'reason'             => sprintf( 'Die URL erscheint bereits, liegt aber mit Position %.1f noch außerhalb der Top 20.', $position ),
					'url'                => $url,
					'query'              => $query,
					'metrics'            => [
						'position'    => $position,
						'impressions' => $impressions,
					],
					'recommended_action' => 'Cluster-Links, Suchintention und Seitentiefe dieser URL schrittweise stärken.',
				]
			);
		}
	}

	$current_pages  = [];
	$previous_pages = [];

	foreach ( (array) ( $snapshot['current_page_rows'] ?? [] ) as $row ) {
		$current_pages[ nexus_normalize_seo_cockpit_url( nexus_get_seo_cockpit_row_key( $row, 0 ) ) ] = $row;
	}

	foreach ( (array) ( $snapshot['previous_page_rows'] ?? [] ) as $row ) {
		$previous_pages[ nexus_normalize_seo_cockpit_url( nexus_get_seo_cockpit_row_key( $row, 0 ) ) ] = $row;
	}

	foreach ( $current_pages as $url => $row ) {
		$current_clicks      = (float) ( $row['clicks'] ?? 0 );
		$current_impressions = (float) ( $row['impressions'] ?? 0 );
		$current_ctr         = (float) ( $row['ctr'] ?? 0 );
		$current_position    = (float) ( $row['position'] ?? 0 );
		$previous_row        = $previous_pages[ $url ] ?? [];
		$previous_clicks     = (float) ( $previous_row['clicks'] ?? 0 );
		$previous_impressions = (float) ( $previous_row['impressions'] ?? 0 );
		$context             = $page_context[ $url ] ?? [];
		$page_role           = nexus_get_seo_cockpit_page_role( $context, $url );
		$role_label          = nexus_get_seo_cockpit_page_role_label( $page_role );
		$is_high_value       = nexus_is_seo_cockpit_high_value_role( $page_role );
		$link_context        = isset( $context['internal_links'] ) && is_array( $context['internal_links'] ) ? $context['internal_links'] : [];
		$context_links       = isset( $link_context['context'] ) && is_array( $link_context['context'] ) ? $link_context['context'] : [];
		$total_links         = isset( $link_context['totals'] ) && is_array( $link_context['totals'] ) ? $link_context['totals'] : [];
		$incoming_documents  = (int) ( $context_links['incoming_documents'] ?? 0 );
		$total_incoming      = (int) ( $total_links['incoming_sources'] ?? 0 );
		$outgoing_unique     = (int) ( $context_links['outgoing_unique_urls'] ?? 0 );
		$indexing_flags      = [];
		$is_virtual_context  = in_array( (string) ( $context['page_type'] ?? '' ), [ 'virtual_cluster', 'legacy_redirect' ], true );

		if ( 'legacy' === $page_role ) {
			continue;
		}

		if ( ( $previous_clicks >= 5 && $current_clicks < ( $previous_clicks * 0.7 ) ) || ( $previous_impressions >= 50 && $current_impressions < ( $previous_impressions * 0.7 ) ) ) {
			$drop = $previous_clicks > 0 ? ( ( $current_clicks - $previous_clicks ) / $previous_clicks ) * 100 : 0;

			$insights[] = nexus_build_seo_cockpit_insight(
				[
					'type'               => 'DECAY',
					'severity'           => $drop <= -50 ? 'high' : 'medium',
					'label'              => 'Traffic-Rückgang auf dieser URL',
					'reason'             => sprintf( 'Klicks oder Impressionen sind gegenüber dem Vergleichsfenster deutlich gefallen (%.1f%%).', $drop ),
					'url'                => $url,
					'query'              => '',
					'metrics'            => [
						'current_clicks'       => $current_clicks,
						'previous_clicks'      => $previous_clicks,
						'current_impressions'  => $current_impressions,
						'previous_impressions' => $previous_impressions,
					],
					'recommended_action' => 'Ändere diese Seite zuerst nicht blind. Prüfe Query-Verschiebungen, Snippet und interne Verlinkung.',
				]
			);
		}

		if ( ! empty( $context['snippet_issues'] ) && $current_impressions >= 30 ) {
			$insights[] = nexus_build_seo_cockpit_insight(
				[
					'type'               => 'SNIPPET_WEAKNESS',
					'severity'           => $current_impressions >= 120 ? 'high' : 'medium',
					'label'              => 'Snippet-Schwäche auf dieser URL',
					'reason'             => sprintf( 'Die Seite sammelt Impressionen, aber Title/Description zeigen Lücken: %s.', implode( ', ', (array) $context['snippet_issues'] ) ),
					'url'                => $url,
					'query'              => '',
					'metrics'            => [
						'impressions'    => $current_impressions,
						'snippet_issues' => $context['snippet_issues'],
					],
					'recommended_action' => 'SEO-Title und Description gegen Suchintention, Klarheit und Länge nachschleifen.',
				]
			);
		}

		if ( $is_high_value && $current_impressions >= 40 && ( $current_position > 12 || ( $current_position <= 12 && $current_ctr < max( 0.01, $overall_ctr * 0.55 ) ) ) ) {
			$insights[] = nexus_build_seo_cockpit_insight(
				[
					'type'               => 'EDITORIAL_PAGE_UNDERPERFORMING',
					'severity'           => $current_impressions >= 120 ? 'high' : 'medium',
					'label'              => sprintf( '%s mit Sichtbarkeit, aber unter Zielwert', $role_label ),
					'reason'             => sprintf( 'Die %s-Seite sammelt %.0f Impressionen, liegt aber bei Position %.1f und %.1f%% CTR.', strtolower( $role_label ), $current_impressions, $current_position, $current_ctr * 100 ),
					'url'                => $url,
					'query'              => '',
					'metrics'            => [
						'clicks'      => $current_clicks,
						'impressions' => $current_impressions,
						'ctr'         => $current_ctr,
						'position'    => $current_position,
					],
					'recommended_action' => 'Snippet, Einstiegsthese und interne Verweise dieser Seite zuerst schärfen.',
				]
			);
		}

		if ( $is_high_value && $incoming_documents <= 1 && $total_incoming <= 3 ) {
			$insights[] = nexus_build_seo_cockpit_insight(
				[
					'type'               => 'ORPHAN_VALUE_PAGE',
					'severity'           => $current_impressions >= 80 ? 'high' : 'medium',
					'label'              => sprintf( '%s bekommt zu wenig Kontextlinks', $role_label ),
					'reason'             => sprintf( 'Die Seite ist für die Wissensarchitektur wichtig, hat aber nur %d kontextuelle Eingangsdokumente und insgesamt %d verlinkende Quellen.', $incoming_documents, $total_incoming ),
					'url'                => $url,
					'query'              => '',
					'metrics'            => [
						'impressions'        => $current_impressions,
						'context_documents'  => $incoming_documents,
						'total_sources'      => $total_incoming,
					],
					'recommended_action' => 'Aus Archiven, Dossiers, Glossar und angrenzenden Essays gezielt Kontextlinks aufbauen.',
				]
			);
		}

		if ( in_array( $page_role, [ 'essay', 'note', 'hub', 'topic' ], true ) && $current_impressions >= 100 && $outgoing_unique <= 1 ) {
			$insights[] = nexus_build_seo_cockpit_insight(
				[
					'type'               => 'WEAK_KNOWLEDGE_BRIDGE',
					'severity'           => $current_impressions >= 250 ? 'high' : 'medium',
					'label'              => sprintf( '%s mit Sichtbarkeit, aber schwacher Wissensbrücke', $role_label ),
					'reason'             => sprintf( 'Die Seite sammelt %.0f Impressionen, führt im Inhalt aber nur auf %d eindeutige interne Ziele weiter.', $current_impressions, $outgoing_unique ),
					'url'                => $url,
					'query'              => '',
					'metrics'            => [
						'impressions'         => $current_impressions,
						'outgoing_unique_urls' => $outgoing_unique,
					],
					'recommended_action' => 'Im Inhalt 2 bis 3 klare Brücken zu Dossiers, Glossar oder vertiefenden Essays ergänzen.',
				]
			);
		}

		if ( ! empty( $context['noindex'] ) ) {
			$indexing_flags[] = 'noindex';
		}

		if ( empty( $context['in_sitemap'] ) && ! $is_virtual_context ) {
			$indexing_flags[] = 'not_in_sitemap';
		}

		if ( empty( $context['canonical_present'] ) ) {
			$indexing_flags[] = 'canonical_missing';
		}

		if ( $is_high_value && ! empty( $indexing_flags ) ) {
			$insights[] = nexus_build_seo_cockpit_insight(
				[
					'type'               => 'INDEXING_MISMATCH',
					'severity'           => ! empty( $context['noindex'] ) ? 'critical' : 'high',
					'label'              => sprintf( '%s mit Indexierungs-Lücke', $role_label ),
					'reason'             => sprintf( 'Die Seite ist redaktionell wichtig, hat aber technische SEO-Signale mit Reibung: %s.', implode( ', ', $indexing_flags ) ),
					'url'                => $url,
					'query'              => '',
					'metrics'            => [
						'impressions'    => $current_impressions,
						'indexing_flags' => $indexing_flags,
					],
					'recommended_action' => 'noindex, Canonical und Sitemap-Status für diese Seite zuerst bereinigen.',
				]
			);
		}
	}

	$grouped_queries = [];

	foreach ( (array) ( $snapshot['query_page_rows'] ?? [] ) as $row ) {
		$query       = nexus_get_seo_cockpit_row_key( $row, 1 );
		$normalized  = nexus_normalize_seo_cockpit_query( $query );
		$raw_url     = nexus_normalize_seo_cockpit_url( nexus_get_seo_cockpit_row_key( $row, 0 ) );
		$url         = nexus_get_seo_cockpit_effective_insight_url( $raw_url );
		$impressions = (float) ( $row['impressions'] ?? 0 );
		$position    = (float) ( $row['position'] ?? 0 );

		if ( '' === $normalized || '' === $url || $impressions < 10 || nexus_is_seo_cockpit_non_target_query( $query ) ) {
			continue;
		}

		if ( ! isset( $grouped_queries[ $normalized ] ) ) {
			$grouped_queries[ $normalized ] = [
				'query'           => $query,
				'total_impressions' => 0.0,
				'urls'            => [],
			];
		}

		$grouped_queries[ $normalized ]['total_impressions'] += $impressions;

		if ( ! isset( $grouped_queries[ $normalized ]['urls'][ $url ] ) ) {
			$grouped_queries[ $normalized ]['urls'][ $url ] = [
				'url'          => $url,
				'impressions'  => 0.0,
				'position_sum' => 0.0,
				'position'     => 0.0,
			];
		}

		$grouped_queries[ $normalized ]['urls'][ $url ]['impressions'] += $impressions;
		$grouped_queries[ $normalized ]['urls'][ $url ]['position_sum'] += $position * max( 1, $impressions );
		$grouped_queries[ $normalized ]['urls'][ $url ]['position']      = $grouped_queries[ $normalized ]['urls'][ $url ]['position_sum'] / max( 1, $grouped_queries[ $normalized ]['urls'][ $url ]['impressions'] );
	}

	foreach ( $grouped_queries as $group ) {
		if ( count( $group['urls'] ) < 2 || $group['total_impressions'] < 50 ) {
			continue;
		}

		$urls = array_values( $group['urls'] );
		usort(
			$urls,
			static function ( $left, $right ) {
				return ( $right['impressions'] <=> $left['impressions'] );
			}
		);

		$top_urls = array_slice( $urls, 0, 5 );

		$insights[] = nexus_build_seo_cockpit_insight(
			[
				'type'               => 'POSSIBLE_CANNIBALIZATION',
				'severity'           => count( $urls ) >= 3 ? 'high' : 'medium',
				'label'              => sprintf( 'Mögliche Kannibalisierung für "%s"', $group['query'] ),
				'reason'             => sprintf( 'Mehrere URLs sammeln für dieselbe Query Impressionen (gesamt %.0f).', $group['total_impressions'] ),
				'url'                => (string) ( $top_urls[0]['url'] ?? '' ),
				'query'              => (string) $group['query'],
				'metrics'            => [
					'urls'              => $top_urls,
					'url_count'         => count( $urls ),
					'total_impressions' => $group['total_impressions'],
				],
				'recommended_action' => 'Primärseite festlegen und interne Links sowie Snippets auf diese URL konzentrieren.',
			]
		);
	}

	$deduped = [];

	foreach ( $insights as $insight ) {
		$key = implode(
			'|',
			[
				(string) $insight['type'],
				(string) $insight['url'],
				(string) $insight['query'],
			]
		);

		if ( isset( $seen[ $key ] ) ) {
			continue;
		}

		$seen[ $key ] = true;
		$deduped[]    = nexus_enrich_seo_cockpit_insight_priority( $insight, $snapshot );
	}

	usort(
		$deduped,
		static function ( $left, $right ) {
			$priority_diff = (int) ( $right['priority_score'] ?? 0 ) <=> (int) ( $left['priority_score'] ?? 0 );

			if ( 0 !== $priority_diff ) {
				return $priority_diff;
			}

			$severity_diff = nexus_get_seo_cockpit_severity_score( (string) ( $right['severity'] ?? '' ) ) <=> nexus_get_seo_cockpit_severity_score( (string) ( $left['severity'] ?? '' ) );

			if ( 0 !== $severity_diff ) {
				return $severity_diff;
			}

			$left_impressions  = (float) ( $left['metrics']['impressions'] ?? $left['metrics']['total_impressions'] ?? 0 );
			$right_impressions = (float) ( $right['metrics']['impressions'] ?? $right['metrics']['total_impressions'] ?? 0 );

			return $right_impressions <=> $left_impressions;
		}
	);

	return array_slice( $deduped, 0, 20 );
}

/**
 * Build a compact problem-page table from snapshot insights and contexts.
 *
 * @param array<string, mixed> $snapshot Snapshot payload.
 * @return array<int, array<string, mixed>>
 */
function nexus_get_seo_cockpit_problem_pages( $snapshot ) {
	$pages         = [];
	$page_contexts = isset( $snapshot['page_contexts'] ) && is_array( $snapshot['page_contexts'] ) ? $snapshot['page_contexts'] : [];

	foreach ( (array) ( $snapshot['current_page_rows'] ?? [] ) as $row ) {
		$url = nexus_normalize_seo_cockpit_url( nexus_get_seo_cockpit_row_key( $row, 0 ) );
		if ( '' === $url ) {
			continue;
		}

		$pages[ $url ] = [
			'url'           => $url,
			'row'           => $row,
			'context'       => $page_contexts[ $url ] ?? nexus_get_seo_cockpit_wp_context_for_url( $url ),
			'insights'      => [],
			'primary'       => null,
			'detail_url'    => nexus_get_seo_cockpit_detail_url( $url ),
		];
	}

	foreach ( (array) ( $snapshot['insights'] ?? [] ) as $insight ) {
		$url = nexus_normalize_seo_cockpit_url( (string) ( $insight['url'] ?? '' ) );
		if ( '' === $url || ! isset( $pages[ $url ] ) ) {
			continue;
		}

		$pages[ $url ]['insights'][] = $insight;

		if ( null === $pages[ $url ]['primary'] || (int) ( $insight['priority_score'] ?? 0 ) > (int) ( $pages[ $url ]['primary']['priority_score'] ?? 0 ) ) {
			$pages[ $url ]['primary'] = $insight;
		}
	}

	$pages = array_values(
		array_filter(
			$pages,
			static function ( $page ) {
				return ! empty( $page['primary'] );
			}
		)
	);

	usort(
		$pages,
		static function ( $left, $right ) {
			$priority_diff = (int) ( $right['primary']['priority_score'] ?? 0 ) <=> (int) ( $left['primary']['priority_score'] ?? 0 );

			if ( 0 !== $priority_diff ) {
				return $priority_diff;
			}

			return (float) ( $right['row']['impressions'] ?? 0 ) <=> (float) ( $left['row']['impressions'] ?? 0 );
		}
	);

	return array_slice( $pages, 0, 10 );
}

/**
 * Return the detail payload for one URL drilldown.
 *
 * @param string   $url       Frontend URL.
 * @param bool     $force     Force refresh.
 * @param int|null $range_days Optional selected range.
 * @return array<string, mixed>|WP_Error
 */
function nexus_get_seo_cockpit_url_detail( $url, $force = false, $range_days = null ) {
	$property   = nexus_get_seo_cockpit_property();
	$url        = nexus_normalize_seo_cockpit_url( $url );
	$range_days = null === $range_days ? nexus_get_seo_cockpit_requested_range_days() : absint( $range_days );
	$ranges     = nexus_get_seo_cockpit_date_ranges( $range_days );
	$query_cap  = nexus_get_seo_cockpit_row_cap( 'detail_queries' );
	$device_cap = nexus_get_seo_cockpit_row_cap( 'detail_devices' );

	if ( '' === $property || '' === $url ) {
		return new WP_Error( 'nexus_seo_missing_detail_context', 'Für den URL-Drilldown fehlt Property oder URL.' );
	}

	$cache_key = nexus_get_seo_cockpit_cache_key( 'detail', [ $property, $range_days, $url ] );
	$cached    = get_transient( $cache_key );

	if ( ! $force && is_array( $cached ) ) {
		return $cached;
	}

	$filters = [
		[
			'dimension'  => 'page',
			'expression' => $url,
		],
	];

	$current = nexus_get_seo_cockpit_aggregate_metrics( $property, $ranges['current_start'], $ranges['current_end'], $filters );
	if ( is_wp_error( $current ) ) {
		return $current;
	}

	$previous = nexus_get_seo_cockpit_aggregate_metrics( $property, $ranges['previous_start'], $ranges['previous_end'], $filters );
	if ( is_wp_error( $previous ) ) {
		return $previous;
	}

	$trend = nexus_get_seo_cockpit_date_series( $property, $ranges['current_start'], $ranges['current_end'], $filters );
	if ( is_wp_error( $trend ) ) {
		return $trend;
	}

	$queries = nexus_get_seo_cockpit_report_rows(
		$property,
		$ranges['current_start'],
		$ranges['current_end'],
		[ 'query' ],
		$filters,
		(int) $query_cap['limit'],
		$query_cap
	);
	if ( is_wp_error( $queries ) ) {
		return $queries;
	}

	$previous_queries = nexus_get_seo_cockpit_report_rows(
		$property,
		$ranges['previous_start'],
		$ranges['previous_end'],
		[ 'query' ],
		$filters,
		(int) $query_cap['limit'],
		$query_cap
	);
	if ( is_wp_error( $previous_queries ) ) {
		return $previous_queries;
	}

	$devices = nexus_get_seo_cockpit_report_rows(
		$property,
		$ranges['current_start'],
		$ranges['current_end'],
		[ 'device' ],
		$filters,
		(int) $device_cap['limit'],
		$device_cap
	);
	if ( is_wp_error( $devices ) ) {
		return $devices;
	}

	$snapshot   = nexus_get_seo_cockpit_snapshot( false, $range_days );
	$insights   = [];
	$context    = nexus_get_seo_cockpit_wp_context_for_url( $url );
	$inspection = function_exists( 'nexus_get_seo_cockpit_cached_url_inspection' ) ? nexus_get_seo_cockpit_cached_url_inspection( $url ) : null;
	$koko       = function_exists( 'nexus_get_seo_cockpit_koko_detail_data' ) ? nexus_get_seo_cockpit_koko_detail_data( $url, $context, $ranges ) : [];
	$leads      = function_exists( 'nexus_get_seo_cockpit_lead_detail_data' ) ? nexus_get_seo_cockpit_lead_detail_data( $url, $ranges ) : [];
	$diagnostics = function_exists( 'nexus_get_seo_cockpit_diagnostics' ) ? nexus_get_seo_cockpit_diagnostics( $url ) : [];

	if ( ! is_wp_error( $snapshot ) ) {
		foreach ( (array) ( $snapshot['insights'] ?? [] ) as $insight ) {
			if ( $url === nexus_normalize_seo_cockpit_url( (string) ( $insight['url'] ?? '' ) ) ) {
				$insights[] = $insight;
			}
		}
	}

	$detail = [
		'generated_at'     => current_time( 'timestamp' ),
		'property'         => $property,
		'url'              => $url,
		'range_days'       => $range_days,
		'ranges'           => $ranges,
		'overview'         => [
			'current'  => $current,
			'previous' => $previous,
		],
		'trend'            => $trend,
		'top_queries'      => $queries,
		'previous_queries' => $previous_queries,
		'devices'          => $devices,
		'context'          => $context,
		'insights'         => $insights,
		'inspection'       => $inspection,
		'koko'             => $koko,
		'leads'            => $leads,
		'diagnostics'      => $diagnostics,
	];

	set_transient( $cache_key, $detail, nexus_get_seo_cockpit_refresh_interval_seconds() );

	return $detail;
}

/**
 * Compute Striking-Distance / Quick-Win opportunities from the snapshot.
 *
 * Returns rows that combine page + query with position 4-15, high impressions,
 * and low CTR — the easiest traffic to win without ranking improvement effort.
 *
 * @param array<string, mixed> $snapshot Snapshot payload.
 * @param int                  $limit    Max rows.
 * @return array<int, array<string, mixed>>
 */
function nexus_get_seo_cockpit_quick_wins( $snapshot, $limit = 12 ) {
	$rows = (array) ( $snapshot['query_page_rows'] ?? [] );

	if ( empty( $rows ) ) {
		return [];
	}

	$previous = [];
	foreach ( (array) ( $snapshot['previous_query_page_rows'] ?? [] ) as $prev_row ) {
		$prev_page  = (string) ( $prev_row['keys'][0] ?? '' );
		$prev_query = (string) ( $prev_row['keys'][1] ?? '' );
		if ( '' === $prev_page || '' === $prev_query ) {
			continue;
		}
		$previous[ $prev_page . '|' . $prev_query ] = $prev_row;
	}

	$candidates = [];
	foreach ( $rows as $row ) {
		$page        = (string) ( $row['keys'][0] ?? '' );
		$query       = (string) ( $row['keys'][1] ?? '' );
		$impressions = (float) ( $row['impressions'] ?? 0 );
		$position    = (float) ( $row['position'] ?? 0 );
		$clicks      = (float) ( $row['clicks'] ?? 0 );

		if ( '' === $page || '' === $query || nexus_is_seo_cockpit_non_target_query( $query ) ) {
			continue;
		}

		if ( $impressions < 20 ) {
			continue;
		}

		if ( $position < 3.5 || $position > 20.0 ) {
			continue;
		}

		// Opportunity score: more impressions + closer to page 1 = higher value.
		// Position weight curve: pos 4 ≈ 1.0, pos 10 ≈ 0.6, pos 15 ≈ 0.35, pos 20 ≈ 0.15.
		$position_weight = max( 0.0, 1.0 - ( ( $position - 3.0 ) / 18.0 ) );
		$score           = $impressions * $position_weight;

		$prev_row    = $previous[ $page . '|' . $query ] ?? [];
		$prev_clicks = (float) ( $prev_row['clicks'] ?? 0 );

		$candidates[] = [
			'page'         => $page,
			'query'        => $query,
			'impressions'  => $impressions,
			'clicks'       => $clicks,
			'ctr'          => (float) ( $row['ctr'] ?? 0 ),
			'position'     => $position,
			'score'        => $score,
			'prev_clicks'  => $prev_clicks,
			'click_delta'  => $clicks - $prev_clicks,
			'detail_url'   => nexus_get_seo_cockpit_detail_url( $page ),
		];
	}

	usort(
		$candidates,
		static function ( $a, $b ) {
			return ( $b['score'] ?? 0 ) <=> ( $a['score'] ?? 0 );
		}
	);

	return array_slice( $candidates, 0, max( 1, absint( $limit ) ) );
}

/**
 * Compute query-level gainers and losers between current and previous period.
 *
 * @param array<string, mixed> $snapshot Snapshot payload.
 * @param int                  $limit    Max rows per side.
 * @return array{gainers:array<int, array<string, mixed>>, losers:array<int, array<string, mixed>>}
 */
function nexus_get_seo_cockpit_query_movers( $snapshot, $limit = 5 ) {
	$current  = [];
	$previous = [];

	foreach ( (array) ( $snapshot['query_page_rows'] ?? [] ) as $row ) {
		$query = (string) ( $row['keys'][1] ?? '' );
		if ( '' === $query || nexus_is_seo_cockpit_non_target_query( $query ) ) {
			continue;
		}
		if ( ! isset( $current[ $query ] ) ) {
			$current[ $query ] = [ 'clicks' => 0.0, 'impressions' => 0.0, 'position_weighted' => 0.0 ];
		}
		$current[ $query ]['clicks']            += (float) ( $row['clicks'] ?? 0 );
		$current[ $query ]['impressions']       += (float) ( $row['impressions'] ?? 0 );
		$current[ $query ]['position_weighted'] += (float) ( $row['impressions'] ?? 0 ) * (float) ( $row['position'] ?? 0 );
	}

	foreach ( (array) ( $snapshot['previous_query_page_rows'] ?? [] ) as $row ) {
		$query = (string) ( $row['keys'][1] ?? '' );
		if ( '' === $query || nexus_is_seo_cockpit_non_target_query( $query ) ) {
			continue;
		}
		if ( ! isset( $previous[ $query ] ) ) {
			$previous[ $query ] = [ 'clicks' => 0.0, 'impressions' => 0.0 ];
		}
		$previous[ $query ]['clicks']      += (float) ( $row['clicks'] ?? 0 );
		$previous[ $query ]['impressions'] += (float) ( $row['impressions'] ?? 0 );
	}

	$movers = [];
	$queries = array_unique( array_merge( array_keys( $current ), array_keys( $previous ) ) );

	foreach ( $queries as $query ) {
		$current_clicks  = (float) ( $current[ $query ]['clicks'] ?? 0 );
		$previous_clicks = (float) ( $previous[ $query ]['clicks'] ?? 0 );
		$delta           = $current_clicks - $previous_clicks;
		$impressions     = (float) ( $current[ $query ]['impressions'] ?? 0 );
		$position        = $impressions > 0
			? ( (float) ( $current[ $query ]['position_weighted'] ?? 0 ) ) / $impressions
			: 0.0;

		// Skip insignificant movements.
		if ( abs( $delta ) < 1 && $current_clicks < 3 && $previous_clicks < 3 ) {
			continue;
		}

		$movers[] = [
			'query'           => $query,
			'current_clicks'  => $current_clicks,
			'previous_clicks' => $previous_clicks,
			'delta'           => $delta,
			'impressions'     => $impressions,
			'position'        => $position,
		];
	}

	$gainers = array_filter(
		$movers,
		static function ( $row ) {
			return $row['delta'] > 0;
		}
	);
	$losers = array_filter(
		$movers,
		static function ( $row ) {
			return $row['delta'] < 0;
		}
	);

	usort(
		$gainers,
		static function ( $a, $b ) {
			return ( $b['delta'] ?? 0 ) <=> ( $a['delta'] ?? 0 );
		}
	);

	usort(
		$losers,
		static function ( $a, $b ) {
			return ( $a['delta'] ?? 0 ) <=> ( $b['delta'] ?? 0 );
		}
	);

	$limit = max( 1, absint( $limit ) );

	return [
		'gainers' => array_values( array_slice( $gainers, 0, $limit ) ),
		'losers'  => array_values( array_slice( $losers, 0, $limit ) ),
	];
}
