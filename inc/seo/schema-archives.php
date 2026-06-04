<?php
/**
 * SEO Archive/Page Schema — Hasimuener Journal
 *
 * JSON-LD for collection pages and mission/about page.
 *
 * @package Hasimuener_Journal
 */

defined( 'ABSPATH' ) || exit;

/* =========================================
   CollectionPage Schema für Archive
   ========================================= */

/**
 * Injiziert CollectionPage JSON-LD für CPT-Archive und die Topic-Taxonomie.
 *
 * Felder: name, description, url, isPartOf (WebSite), inLanguage,
 * mainEntity (ItemList mit den auf der Seite gelisteten Beiträgen).
 *
 * Wirkt nur auf Seite 1 — paginierte Archive sind ohnehin noindex.
 */
function hp_archive_jsonld_schema(): void {
	$is_cpt_archive = is_post_type_archive( [ 'essay', 'note', 'glossar', 'dossier' ] );
	$is_topic_tax   = is_tax( 'topic' );

	if ( ( ! $is_cpt_archive && ! $is_topic_tax ) || is_paged() ) {
		return;
	}

	$obj = get_queried_object();

	if ( $is_cpt_archive ) {
		if ( ! ( $obj instanceof WP_Post_Type ) ) {
			return;
		}

		$name = post_type_archive_title( '', false );
		$url  = get_post_type_archive_link( $obj->name );
		$desc = ! empty( $obj->description ) ? wp_strip_all_tags( $obj->description ) : '';
	} else {
		if ( ! ( $obj instanceof WP_Term ) ) {
			return;
		}

		$name = single_term_title( '', false );
		$url  = get_term_link( $obj );
		$desc = wp_strip_all_tags( term_description() );
	}

	if ( is_wp_error( $url ) || ! is_string( $url ) || '' === $url ) {
		return;
	}

	$schema = [
		'@context'   => 'https://schema.org',
		'@type'      => 'CollectionPage',
		'@id'        => $url . '#collection',
		'name'       => $name,
		'url'        => $url,
		'inLanguage' => get_locale(),
		'isPartOf'   => [ '@id' => hp_schema_site_entity_id( 'website' ) ],
		'publisher'  => [ '@id' => hp_schema_site_entity_id( 'organization' ) ],
	];

	if ( $desc ) {
		$schema['description'] = $desc;
	}

	if ( $is_topic_tax && $obj instanceof WP_Term ) {
		$topic = hp_schema_topic_reference( $obj );
		if ( null !== $topic ) {
			if ( $desc ) {
				$topic['description'] = $desc;
			}

			$schema['about'] = $topic;
		}
	}

	// ItemList mit den aktuell ausgegebenen Beiträgen (max. 20)
	global $wp_query;
	if ( $wp_query instanceof WP_Query && $wp_query->have_posts() ) {
		$items = [];
		$pos   = 1;

		foreach ( $wp_query->posts as $p ) {
			if ( $pos > 20 ) {
				break;
			}
			if ( ! ( $p instanceof WP_Post ) ) {
				continue;
			}
			$items[] = [
				'@type'    => 'ListItem',
				'position' => $pos++,
				'name'     => get_the_title( $p ),
				'item'     => [
					'@type' => hp_schema_post_type_name( $p ),
					'@id'   => hp_schema_post_entity_id( $p ),
					'url'   => get_permalink( $p ),
					'name'  => get_the_title( $p ),
				],
			];
		}

		if ( $items ) {
			$schema['mainEntity'] = [
				'@type'           => 'ItemList',
				'itemListElement' => $items,
			];
		}
	}

	echo "\n<!-- Haşim Üner: CollectionPage JSON-LD -->\n";
	echo '<script type="application/ld+json">';
	echo wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
	echo "</script>\n";
}
add_action( 'wp_head', 'hp_archive_jsonld_schema', 5 );

/* =========================================
   AboutPage Schema für /mission/
   ========================================= */

/**
 * Injiziert AboutPage JSON-LD für die Mission-Seite.
 *
 * Semantisch korrekt: /mission/ ist eine narrative Selbstdarstellung,
 * keine Q&A-Sammlung — daher AboutPage statt FAQPage.
 * Verknüpft per `mainEntity` mit Person + Organization aus dem
 * globalen Graph (#person, #organization), damit Google die Seite
 * als Author-Entity-Beleg lesen kann.
 */
function hp_mission_jsonld_schema(): void {
	if ( ! function_exists( 'hp_is_mission_page' ) || ! hp_is_mission_page() ) {
		return;
	}

	$post = hp_schema_get_queried_post();
	if ( null === $post ) {
		return;
	}

	$permalink = get_permalink( $post );
	if ( ! is_string( $permalink ) || '' === $permalink ) {
		return;
	}

	$person_id = hp_schema_site_entity_id( 'person' );
	$org_id    = hp_schema_site_entity_id( 'organization' );
	$site_id   = hp_schema_site_entity_id( 'website' );

	$schema = [
		'@context'         => 'https://schema.org',
		'@type'            => 'AboutPage',
		'@id'              => $permalink . '#aboutpage',
		'name'             => get_the_title( $post ),
		'description'      => function_exists( 'hp_get_meta_description' )
			? hp_get_meta_description()
			: '',
		'url'              => $permalink,
		'inLanguage'       => get_locale(),
		'isPartOf'         => [ '@id' => $site_id ],
		'about'            => [ '@id' => $person_id ],
		'mainEntity'       => [ '@id' => $person_id ],
		'publisher'        => [ '@id' => $org_id ],
		'mainEntityOfPage' => [
			'@type' => 'WebPage',
			'@id'   => $permalink,
		],
	];

	$image = hp_get_schema_image();
	if ( $image ) {
		$schema['image'] = $image;
	}

	echo "\n<!-- Haşim Üner: AboutPage JSON-LD -->\n";
	echo '<script type="application/ld+json">';
	echo wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
	echo "</script>\n";
}
add_action( 'wp_head', 'hp_mission_jsonld_schema', 5 );
