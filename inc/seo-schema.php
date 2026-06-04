<?php
/**
 * SEO Schema loader — Hasimuener Journal
 *
 * Compatibility entrypoint for JSON-LD modules. The concrete schema logic
 * lives in inc/seo/schema-*.php to keep each context independently readable.
 *
 * @package Hasimuener_Journal
 */

defined( 'ABSPATH' ) || exit;

$hp_schema_dir = __DIR__ . '/seo';

require_once $hp_schema_dir . '/schema-helpers.php';
require_once $hp_schema_dir . '/schema-site.php';
require_once $hp_schema_dir . '/schema-content.php';
require_once $hp_schema_dir . '/schema-archives.php';

unset( $hp_schema_dir );
