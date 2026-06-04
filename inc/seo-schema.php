<?php
/**
 * SEO: JSON-LD Schema — Hasimuener Journal
 *
 * Strukturierte Daten (Schema.org) als JSON-LD im <head>
 * von Single-Essay-Seiten.
 *
 * Typ: ScholarlyArticle — semantisch passend für
 * analytische Langform-Texte mit Quellenverweisen.
 * Google erkennt diesen Typ und kann ihn in den
 * Knowledge Graph übernehmen.
 *
 * Hinweis: Meta-Description und OG-Tags werden von
 * inc/seo-meta.php verwaltet. Keine doppelte Ausgabe im Theme.
 *
 * @package Hasimuener_Journal
 * @since   4.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * ORCID-iD des Herausgebers. Wird in Person-Schema
 * und als `sameAs`-Verknüpfung ausgegeben.
 */
const HP_ORCID_ID  = '0009-0008-7500-2015';
const HP_ORCID_URL = 'https://orcid.org/' . HP_ORCID_ID;

/**
 * Liefert die kanonische Entity-ID fuer globale Site-Entitaeten.
 *
 * @param string $entity Entity-Schluessel: person, organization, website.
 * @return string
 */
function hp_schema_site_entity_id( string $entity ): string {
	return home_url( '/' ) . '#' . ltrim( $entity, '#' );
}

/**
 * Liefert die kanonische Entity-ID fuer einen Content-Knoten.
 *
 * @param WP_Post|int $post Post oder Post-ID.
 * @return string
 */
function hp_schema_post_entity_id( $post ): string {
	if ( $post instanceof WP_Post ) {
		$post_obj = $post;
	} elseif ( is_numeric( $post ) ) {
		$post_obj = get_post( (int) $post );
	} else {
		return '';
	}

	if ( ! ( $post_obj instanceof WP_Post ) ) {
		return '';
	}

	$permalink = get_permalink( $post_obj );
	if ( ! is_string( $permalink ) || '' === $permalink ) {
		return '';
	}

	$type = get_post_type( $post_obj );
	if ( 'glossar' === $type ) {
		return $permalink . '#term';
	}
	if ( 'dossier' === $type ) {
		return $permalink . '#dossier';
	}

	return $permalink . '#article';
}

/**
 * Liefert die kanonische Entity-ID fuer einen Topic-Knoten.
 *
 * @param WP_Term|int $term Term oder Term-ID.
 * @return string
 */
function hp_schema_topic_entity_id( $term ): string {
	if ( $term instanceof WP_Term ) {
		$term_obj = $term;
	} elseif ( is_numeric( $term ) ) {
		$term_obj = get_term( (int) $term, 'topic' );
	} else {
		return '';
	}

	if ( ! ( $term_obj instanceof WP_Term ) ) {
		return '';
	}

	$link = get_term_link( $term_obj );
	if ( is_wp_error( $link ) || ! is_string( $link ) || '' === $link ) {
		return '';
	}

	return $link . '#topic';
}

/**
 * Liefert eine kompakte Topic-Referenz fuer about/mentions-Felder.
 *
 * @param WP_Term|int $term Term oder Term-ID.
 * @return array<string,mixed>|null
 */
function hp_schema_topic_reference( $term ): ?array {
	if ( $term instanceof WP_Term ) {
		$term_obj = $term;
	} elseif ( is_numeric( $term ) ) {
		$term_obj = get_term( (int) $term, 'topic' );
	} else {
		return null;
	}

	if ( ! ( $term_obj instanceof WP_Term ) ) {
		return null;
	}

	$link = get_term_link( $term_obj );
	if ( is_wp_error( $link ) || ! is_string( $link ) || '' === $link ) {
		return null;
	}

	return [
		'@type' => 'Thing',
		'@id'   => $link . '#topic',
		'name'  => $term_obj->name,
		'url'   => $link,
	];
}

/**
 * Liefert den passenden Schema.org-Typ fuer einen Content-Knoten.
 *
 * @param WP_Post|int $post Post oder Post-ID.
 * @return string
 */
function hp_schema_post_type_name( $post ): string {
	if ( $post instanceof WP_Post ) {
		$post_obj = $post;
	} elseif ( is_numeric( $post ) ) {
		$post_obj = get_post( (int) $post );
	} else {
		return 'CreativeWork';
	}

	$type     = $post_obj instanceof WP_Post ? get_post_type( $post_obj ) : '';

	if ( 'essay' === $type ) {
		return 'ScholarlyArticle';
	}
	if ( 'note' === $type ) {
		return 'BlogPosting';
	}
	if ( 'glossar' === $type ) {
		return 'DefinedTerm';
	}
	if ( 'dossier' === $type ) {
		return 'Article';
	}

	return 'CreativeWork';
}

/**
 * Liefert einen ISO-8601-Zeitstempel aus redaktionellen Datumsstrings.
 *
 * @param string $date_string Datumswert aus Post-Meta.
 * @param string $fallback    Fallback-ISO-Zeitstempel.
 * @return string
 */
function hp_schema_iso_datetime_from_meta_date( string $date_string, string $fallback ): string {
	$date_string = trim( $date_string );
	if ( '' === $date_string ) {
		return $fallback;
	}

	$timestamp = strtotime( $date_string );
	if ( false === $timestamp ) {
		return $fallback;
	}

	return date( 'c', $timestamp );
}

/**
 * Liefert das aktuelle Query-Objekt als Post, falls vorhanden.
 *
 * @return WP_Post|null
 */
function hp_schema_get_queried_post(): ?WP_Post {
	$post = get_queried_object();

	return $post instanceof WP_Post ? $post : null;
}

/**
 * Liefert ein Image-Objekt für JSON-LD aus den vorhandenen
 * Social-Image-Daten. Fallback-Kette identisch zu OG-Image,
 * damit Article-Rich-Results auch ohne Beitragsbild greifen.
 *
 * @return array<string,mixed>|null
 */
function hp_get_schema_image(): ?array {
	if ( ! function_exists( 'hp_get_seo_image_data' ) ) {
		return null;
	}

	$data = hp_get_seo_image_data();
	if ( empty( $data['url'] ) ) {
		return null;
	}

	$image = [
		'@type' => 'ImageObject',
		'url'   => $data['url'],
	];

	if ( ! empty( $data['width'] ) ) {
		$image['width'] = (int) $data['width'];
	}
	if ( ! empty( $data['height'] ) ) {
		$image['height'] = (int) $data['height'];
	}
	if ( ! empty( $data['alt'] ) ) {
		$image['caption'] = $data['alt'];
	}

	return $image;
}

/**
 * Liefert Topic-Begriffe eines Posts als Schema-Felder
 * (`keywords`, `articleSection`, `about`).
 *
 * @param int $post_id
 * @return array{keywords?:string,articleSection?:string,about?:array<int,array<string,mixed>>}
 */
function hp_get_schema_topic_fields( int $post_id ): array {
	$out    = [];
	$topics = get_the_terms( $post_id, 'topic' );

	if ( ! $topics || is_wp_error( $topics ) ) {
		return $out;
	}

	$names = array_values( array_filter( wp_list_pluck( $topics, 'name' ) ) );
	if ( ! $names ) {
		return $out;
	}

	$out['keywords']       = implode( ', ', $names );
	$out['articleSection'] = $names[0];

	$about = [];
	foreach ( $topics as $topic ) {
		$topic_ref = hp_schema_topic_reference( $topic );
		if ( null === $topic_ref ) {
			continue;
		}

		$about[] = $topic_ref;
	}

	if ( $about ) {
		$out['about'] = $about;
	}

	return $out;
}

/**
 * Injiziert ScholarlyArticle JSON-LD für Essay-Singles.
 *
 * Felder: headline, datePublished, dateModified, abstract,
 * author (Person), publisher (Organization), image, wordCount,
 * mainEntityOfPage, inLanguage.
 */
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
   Organization + WebSite Schema (global)
   ========================================= */

/**
 * Gibt Organization + WebSite JSON-LD auf jeder Seite aus.
 *
 * Organization: Repräsentiert das Journal als Herausgeber.
 * WebSite: Bietet Google Sitelinks-Searchbox und verknüpft
 * die Website mit dem Organization-Entity.
 *
 * Wird nur einmal pro Request ausgegeben.
 */
function hp_org_website_jsonld_schema(): void {

	$site_name = get_bloginfo( 'name' );
	$site_url  = home_url( '/' );
	$site_desc = get_bloginfo( 'description' );
	$locale    = get_locale();
	$person_id = hp_schema_site_entity_id( 'person' );
	$org_id    = hp_schema_site_entity_id( 'organization' );
	$site_id   = hp_schema_site_entity_id( 'website' );

	$graph = [
		'@context' => 'https://schema.org',
		'@graph'   => [],
	];

	// --- Person (Herausgeber) ---
	$person = [
		'@type'    => 'Person',
		'@id'      => $person_id,
		'name'     => 'Haşim Üner',
		'url'      => $site_url,
		'jobTitle' => 'Medienwissenschaftler & Publizist',
		'identifier' => [
			'@type'       => 'PropertyValue',
			'propertyID'  => 'ORCID',
			'value'       => HP_ORCID_ID,
			'url'         => HP_ORCID_URL,
		],
		'sameAs'   => [
			HP_ORCID_URL,
			'https://x.com/_0239983326111',
		],
	];
	$graph['@graph'][] = $person;

	// --- Organization ---
	$org = [
		'@type'       => 'Organization',
		'@id'         => $org_id,
		'name'        => $site_name,
		'url'         => $site_url,
		'description' => $site_desc ?: 'Essays und Analysen zu Macht, Medien und Perspektive. Von Haşim Üner.',
		'founder'     => [
			'@id' => $person_id,
		],
		'foundingDate'         => '2024',
		'publishingPrinciples' => $site_url . 'mission/',
	];

	// Logo (Site-Icon als Fallback)
	$site_icon = get_site_icon_url( 512 );
	if ( $site_icon ) {
		$org['logo'] = [
			'@type'      => 'ImageObject',
			'url'        => $site_icon,
			'contentUrl' => $site_icon,
			'caption'    => $site_name,
		];
		$org['image'] = $site_icon;
	}

	// Soziale Profile
	$org['sameAs'] = [
		HP_ORCID_URL,
		'https://x.com/_0239983326111',
	];

	$graph['@graph'][] = $org;

	// --- WebSite ---
	$website = [
		'@type'       => 'WebSite',
		'@id'         => $site_id,
		'name'        => $site_name,
		'url'         => $site_url,
		'description' => $site_desc ?: '',
		'inLanguage'  => $locale,
		'publisher'   => [
			'@id' => $org_id,
		],
	];

	// Sitelinks-Searchbox (Google)
	$website['potentialAction'] = [
		'@type'       => 'SearchAction',
		'target'      => [
			'@type'        => 'EntryPoint',
			'urlTemplate'  => $site_url . '?s={search_term_string}',
		],
		'query-input' => 'required name=search_term_string',
	];

	$graph['@graph'][] = $website;

	echo "\n<!-- Haşim Üner: Organization + WebSite JSON-LD -->\n";
	echo '<script type="application/ld+json">';
	echo wp_json_encode( $graph, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
	echo "</script>\n";
}
add_action( 'wp_head', 'hp_org_website_jsonld_schema', 4 );

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
