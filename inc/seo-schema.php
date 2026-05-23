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
 * (`keywords` String + `articleSection` String).
 *
 * @param int $post_id
 * @return array{keywords?:string,articleSection?:string}
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

	$post    = get_queried_object();
	$excerpt = has_excerpt( $post->ID )
		? wp_strip_all_tags( get_the_excerpt( $post ) )
		: wp_trim_words( wp_strip_all_tags( $post->post_content ), 40, ' …' );

	$schema = [
		'@context'      => 'https://schema.org',
		'@type'         => 'ScholarlyArticle',
		'headline'      => get_the_title( $post ),
		'datePublished' => get_the_date( 'c', $post ),
		'dateModified'  => get_the_modified_date( 'c', $post ),
		'abstract'      => $excerpt,
		'author'        => [
			'@id' => home_url( '/' ) . '#person',
		],
		'publisher'     => [
			'@id' => home_url( '/' ) . '#organization',
		],
		'mainEntityOfPage' => [
			'@type' => 'WebPage',
			'@id'   => get_permalink( $post ),
		],
		'url'           => get_permalink( $post ),
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

	$graph = [
		'@context' => 'https://schema.org',
		'@graph'   => [],
	];

	// --- Person (Herausgeber) ---
	$person = [
		'@type'    => 'Person',
		'@id'      => $site_url . '#person',
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
		'@id'         => $site_url . '#organization',
		'name'        => $site_name,
		'url'         => $site_url,
		'description' => $site_desc ?: 'Essays und Analysen zu Macht, Medien und Perspektive. Von Haşim Üner.',
		'founder'     => [
			'@id' => $site_url . '#person',
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
		'@id'         => $site_url . '#website',
		'name'        => $site_name,
		'url'         => $site_url,
		'description' => $site_desc ?: '',
		'inLanguage'  => $locale,
		'publisher'   => [
			'@id' => $site_url . '#organization',
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

	$post  = get_queried_object();
	$kurz  = get_post_meta( $post->ID, '_hp_glossar_kurz', true );
	$desc  = $kurz
		? wp_strip_all_tags( $kurz )
		: wp_trim_words( wp_strip_all_tags( $post->post_content ), 40, ' …' );

	$permalink = get_permalink( $post );

	$schema = [
		'@context'         => 'https://schema.org',
		'@type'            => 'DefinedTerm',
		'@id'              => $permalink . '#term',
		'name'             => get_the_title( $post ),
		'description'      => $desc,
		'url'              => $permalink,
		'inLanguage'       => get_locale(),
		'mainEntityOfPage' => [
			'@type' => 'WebPage',
			'@id'   => $permalink,
		],
		'inDefinedTermSet' => [
			'@type' => 'DefinedTermSet',
			'name'  => 'Glossar — ' . get_bloginfo( 'name' ),
			'url'   => get_post_type_archive_link( 'glossar' ),
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

	$post    = get_queried_object();
	$excerpt = has_excerpt( $post->ID )
		? wp_strip_all_tags( get_the_excerpt( $post ) )
		: wp_trim_words( wp_strip_all_tags( $post->post_content ), 40, ' …' );

	$schema = [
		'@context'      => 'https://schema.org',
		'@type'         => 'BlogPosting',
		'headline'      => get_the_title( $post ),
		'datePublished' => get_the_date( 'c', $post ),
		'dateModified'  => get_the_modified_date( 'c', $post ),
		'description'   => $excerpt,
		'author'        => [
			'@id' => home_url( '/' ) . '#person',
		],
		'publisher'     => [
			'@id' => home_url( '/' ) . '#organization',
		],
		'mainEntityOfPage' => [
			'@type' => 'WebPage',
			'@id'   => get_permalink( $post ),
		],
		'url'           => get_permalink( $post ),
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

	$post     = get_queried_object();
	$post_id  = (int) $post->ID;
	$intro    = (string) get_post_meta( $post_id, '_hp_dossier_intro', true );
	$version  = (string) get_post_meta( $post_id, '_hp_dossier_version', true );
	$stand    = (string) get_post_meta( $post_id, '_hp_dossier_stand', true );
	$desc     = $intro
		? wp_strip_all_tags( $intro )
		: wp_trim_words( wp_strip_all_tags( $post->post_content ), 40, ' …' );

	$schema = [
		'@context'         => 'https://schema.org',
		'@type'            => 'Article',
		'headline'         => get_the_title( $post ),
		'datePublished'    => get_the_date( 'c', $post ),
		'dateModified'     => $stand ? date( 'c', strtotime( $stand ) ) : get_the_modified_date( 'c', $post ),
		'description'      => $desc,
		'abstract'         => $desc,
		'author'           => [ '@id' => home_url( '/' ) . '#person' ],
		'publisher'        => [ '@id' => home_url( '/' ) . '#organization' ],
		'mainEntityOfPage' => [
			'@type' => 'WebPage',
			'@id'   => get_permalink( $post ),
		],
		'url'              => get_permalink( $post ),
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
					'@type'    => 'essay' === get_post_type( $p ) ? 'ScholarlyArticle' : 'BlogPosting',
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
