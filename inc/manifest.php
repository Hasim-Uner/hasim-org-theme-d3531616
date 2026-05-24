<?php
/**
 * Module manifest — Hasimuener Journal
 *
 * Central list of theme modules in load order. Keep this list explicit:
 * WordPress hooks in later modules may depend on functions registered by
 * earlier modules.
 *
 * @package Hasimuener_Journal
 */

defined( 'ABSPATH' ) || exit;

return [
	'helpers.php',
	'post-types.php',
	'taxonomies.php',
	'enqueue.php',
	'generatepress-compat.php',
	'meta-fields.php',
	'seo-schema.php',
	'seo-meta.php',
	'seo-hygiene.php',
	'glossary.php',
	'link-preview.php',
	'dossier.php',
	'breadcrumbs.php',
	'header-nav.php',
	'comments.php',
	'contacts-admin.php',
	'contact.php',
	'newsletter.php',
	'newsletter-broadcast.php',
	'privacy-maintenance.php',
	'graph-api.php',
	'mini-graph.php',
	'votes.php',
	'votes-api.php',
	'glossar-seed.php',
];
