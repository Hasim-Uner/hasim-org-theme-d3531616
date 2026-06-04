<?php
/**
 * SEO Content Schema — Hasimuener Journal
 *
 * JSON-LD for editorial singles: essays, notes, glossary terms and dossiers.
 *
 * @package Hasimuener_Journal
 */

defined( 'ABSPATH' ) || exit;

function hp_essay_jsonld_schema(): void {
	if ( ! is_singular( 'essay' ) ) {
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

	$site_id   = hp_schema_site_entity_id( 'website' );
	$person_id = hp_schema_site_entity_id( 'person' );
	$org_id    = hp_schema_site_entity_id( 'organization' );
	$entity_id = hp_schema_post_entity_id( $post );
	$excerpt = has_excerpt( $post->ID )
		? wp_strip_all_tags( get_the_excerpt( $post ) )
		: wp_trim_words( wp_strip_all_tags( $post->post_content ), 40, ' …' );

	$schema = [
		'@context'      => 'https://schema.org',
		'@type'         => 'ScholarlyArticle',
		'@id'           => $entity_id,
		'headline'      => get_the_title( $post ),
		'datePublished' => get_the_date( 'c', $post ),
		'dateModified'  => get_the_modified_date( 'c', $post ),
		'abstract'      => $excerpt,
		'author'        => [
			'@id' => $person_id,
		],
		'publisher'     => [
			'@id' => $org_id,
		],
		'isPartOf'      => [ '@id' => $site_id ],
		'mainEntityOfPage' => [
			'@type' => 'WebPage',
			'@id'   => $permalink,
		],
		'url'           => $permalink,
		'inLanguage'    => get_locale(),
	];

	// Beitragsbild — mit Fallback aus Social-Image-Resolver
	$image = hp_get_schema_image();
	if ( $image ) {
		$schema['image'] = $image;
	}

	// Topic-Taxonomie → keywords + articleSection
	$schema = array_merge( $schema, hp_get_schema_topic_fields( (int) $post->ID ) );

	// Wortanzahl — relevantes Signal für Longform-Erkennung
	$word_count = str_word_count( wp_strip_all_tags( $post->post_content ) );
	if ( $word_count > 0 ) {
		$schema['wordCount'] = $word_count;
	}

	echo "\n<!-- Haşim Üner: JSON-LD -->\n";
	echo '<script type="application/ld+json">';
	echo wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
	echo "</script>\n";
}
add_action( 'wp_head', 'hp_essay_jsonld_schema', 5 );
/* =========================================
   DefinedTerm Schema für Glossar
   ========================================= */

/**
 * Injiziert DefinedTerm JSON-LD für Glossar-Singles.
 *
 * Felder: name, description, url, inDefinedTermSet.
 * Ermöglicht Google die Erkennung als Begriffsdefinition.
 */
function hp_glossar_jsonld_schema(): void {
	if ( ! is_singular( 'glossar' ) ) {
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

	$site_id = hp_schema_site_entity_id( 'website' );
	$org_id  = hp_schema_site_entity_id( 'organization' );
	$kurz  = get_post_meta( $post->ID, '_hp_glossar_kurz', true );
	$desc  = $kurz
		? wp_strip_all_tags( $kurz )
		: wp_trim_words( wp_strip_all_tags( $post->post_content ), 40, ' …' );

	$archive_url = get_post_type_archive_link( 'glossar' );

	$schema = [
		'@context'         => 'https://schema.org',
		'@type'            => 'DefinedTerm',
		'@id'              => hp_schema_post_entity_id( $post ),
		'name'             => get_the_title( $post ),
		'description'      => $desc,
		'url'              => $permalink,
		'inLanguage'       => get_locale(),
		'isPartOf'         => [ '@id' => $site_id ],
		'publisher'        => [ '@id' => $org_id ],
		'mainEntityOfPage' => [
			'@type' => 'WebPage',
			'@id'   => $permalink,
		],
		'inDefinedTermSet' => [
			'@type' => 'DefinedTermSet',
			'@id'   => $archive_url ? $archive_url . '#termset' : home_url( '/glossar/' ) . '#termset',
			'name'  => 'Glossar — ' . get_bloginfo( 'name' ),
			'url'   => $archive_url ?: home_url( '/glossar/' ),
		],
	];

	echo "\n<!-- Haşim Üner: Glossar DefinedTerm JSON-LD -->\n";
	echo '<script type="application/ld+json">';
	echo wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
	echo "</script>\n";
}
add_action( 'wp_head', 'hp_glossar_jsonld_schema', 5 );

/* =========================================
   Article Schema für Notizen
   ========================================= */

/**
 * Injiziert Article JSON-LD für Notiz-Singles.
 *
 * Typ BlogPosting — semantisch passend für kürzere
 * Beobachtungen und Einordnungen.
 */
function hp_note_jsonld_schema(): void {
	if ( ! is_singular( 'note' ) ) {
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

	$site_id   = hp_schema_site_entity_id( 'website' );
	$person_id = hp_schema_site_entity_id( 'person' );
	$org_id    = hp_schema_site_entity_id( 'organization' );
	$entity_id = hp_schema_post_entity_id( $post );
	$excerpt = has_excerpt( $post->ID )
		? wp_strip_all_tags( get_the_excerpt( $post ) )
		: wp_trim_words( wp_strip_all_tags( $post->post_content ), 40, ' …' );

	$schema = [
		'@context'      => 'https://schema.org',
		'@type'         => 'BlogPosting',
		'@id'           => $entity_id,
		'headline'      => get_the_title( $post ),
		'datePublished' => get_the_date( 'c', $post ),
		'dateModified'  => get_the_modified_date( 'c', $post ),
		'description'   => $excerpt,
		'author'        => [
			'@id' => $person_id,
		],
		'publisher'     => [
			'@id' => $org_id,
		],
		'isPartOf'      => [ '@id' => $site_id ],
		'mainEntityOfPage' => [
			'@type' => 'WebPage',
			'@id'   => $permalink,
		],
		'url'           => $permalink,
		'inLanguage'    => get_locale(),
	];

	$image = hp_get_schema_image();
	if ( $image ) {
		$schema['image'] = $image;
	}

	$schema = array_merge( $schema, hp_get_schema_topic_fields( (int) $post->ID ) );

	$word_count = str_word_count( wp_strip_all_tags( $post->post_content ) );
	if ( $word_count > 0 ) {
		$schema['wordCount'] = $word_count;
	}

	echo "\n<!-- Haşim Üner: Note BlogPosting JSON-LD -->\n";
	echo '<script type="application/ld+json">';
	echo wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
	echo "</script>\n";
}
add_action( 'wp_head', 'hp_note_jsonld_schema', 5 );

/* =========================================
   Article-Schema für Dossiers (Pillar 4)
   ========================================= */

/**
 * Injiziert Article JSON-LD für Dossier-Singles.
 *
 * Strategischer Zweck: Dossiers als zitierfähige
 * Wissensknoten in den Google Knowledge Graph
 * einspeisen. hasPart referenziert die Leseplan-
 * Beiträge, mentions die Begriffe im Apparat.
 * citation enthält die APA-Zitation als String —
 * macht das Dossier für Citation-Scraper (Google
 * Scholar, Semantic Scholar) maschinell lesbar.
 */
function hp_dossier_jsonld_schema(): void {
	if ( ! is_singular( 'dossier' ) ) {
		return;
	}

	$post     = hp_schema_get_queried_post();
	if ( null === $post ) {
		return;
	}

	$permalink = get_permalink( $post );
	if ( ! is_string( $permalink ) || '' === $permalink ) {
		return;
	}

	$post_id  = (int) $post->ID;
	$site_id  = hp_schema_site_entity_id( 'website' );
	$person_id = hp_schema_site_entity_id( 'person' );
	$org_id    = hp_schema_site_entity_id( 'organization' );
	$entity_id = hp_schema_post_entity_id( $post );
	$intro    = (string) get_post_meta( $post_id, '_hp_dossier_intro', true );
	$version  = (string) get_post_meta( $post_id, '_hp_dossier_version', true );
	$stand    = (string) get_post_meta( $post_id, '_hp_dossier_stand', true );
	$desc     = $intro
		? wp_strip_all_tags( $intro )
		: wp_trim_words( wp_strip_all_tags( $post->post_content ), 40, ' …' );

	$schema = [
		'@context'         => 'https://schema.org',
		'@type'            => 'Article',
		'@id'              => $entity_id,
		'headline'         => get_the_title( $post ),
		'datePublished'    => get_the_date( 'c', $post ),
		'dateModified'     => hp_schema_iso_datetime_from_meta_date( $stand, get_the_modified_date( 'c', $post ) ),
		'description'      => $desc,
		'abstract'         => $desc,
		'author'           => [ '@id' => $person_id ],
		'publisher'        => [ '@id' => $org_id ],
		'isPartOf'         => [ '@id' => $site_id ],
		'mainEntityOfPage' => [
			'@type' => 'WebPage',
			'@id'   => $permalink,
		],
		'url'              => $permalink,
		'inLanguage'       => get_locale(),
	];

	if ( $version ) {
		$schema['version'] = $version;
	}

	$image = hp_get_schema_image();
	if ( $image ) {
		$schema['image'] = $image;
	}

	$schema = array_merge( $schema, hp_get_schema_topic_fields( $post_id ) );

	// hasPart: Leseplan-Beiträge als Article-Referenzen
	if ( function_exists( 'hp_dossier_get_leseplan' ) ) {
		$leseplan = hp_dossier_get_leseplan( $post_id );
		if ( $leseplan ) {
			$schema['hasPart'] = array_map( function ( $p ) {
				return [
					'@type'    => hp_schema_post_type_name( $p ),
					'@id'      => hp_schema_post_entity_id( $p ),
					'headline' => get_the_title( $p ),
					'url'      => get_permalink( $p ),
				];
			}, $leseplan );
		}
	}

	// mentions: Begriffsapparat als DefinedTerm-Referenzen
	if ( function_exists( 'hp_dossier_get_begriffe' ) ) {
		$begriffe = hp_dossier_get_begriffe( $post_id );
		if ( $begriffe ) {
			$schema['mentions'] = array_map( function ( $b ) {
				return [
					'@type' => 'DefinedTerm',
					'@id'   => hp_schema_post_entity_id( $b ),
					'name'  => get_the_title( $b ),
					'url'   => get_permalink( $b ),
				];
			}, $begriffe );
		}
	}

	// citation: APA-Zitation als String — Citation-Scraper-Bait
	if ( function_exists( 'hp_dossier_get_citations' ) ) {
		$c = hp_dossier_get_citations( $post_id );
		if ( ! empty( $c['apa'] ) ) {
			$schema['citation'] = $c['apa'];
		}
	}

	echo "\n<!-- Haşim Üner: Dossier Article JSON-LD -->\n";
	echo '<script type="application/ld+json">';
	echo wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
	echo "</script>\n";
}
add_action( 'wp_head', 'hp_dossier_jsonld_schema', 5 );
