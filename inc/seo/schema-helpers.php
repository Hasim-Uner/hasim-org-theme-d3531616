<?php
/**
 * SEO Schema helpers — Hasimuener Journal
 *
 * Shared Entity-ID, URL and field helpers for JSON-LD modules.
 *
 * @package Hasimuener_Journal
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
