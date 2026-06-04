<?php
/**
 * SEO Site Schema — Hasimuener Journal
 *
 * Global Person, Organization and WebSite JSON-LD.
 *
 * @package Hasimuener_Journal
 */

defined( 'ABSPATH' ) || exit;

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
