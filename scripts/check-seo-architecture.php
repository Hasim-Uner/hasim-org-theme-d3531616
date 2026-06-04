<?php
/**
 * SEO architecture regression checks.
 *
 * Lightweight static checks for invariants that do not need a WordPress
 * runtime: schema module boundaries, URL helper ownership and SEO edge-case
 * guards.
 *
 * @package Hasimuener_Journal
 */

declare(strict_types=1);

$root = dirname(__DIR__);

/**
 * Reads a repository file.
 *
 * @param string $relative_path Relative file path.
 * @return string
 */
function hp_check_read(string $relative_path): string {
	global $root;

	$path = $root . '/' . $relative_path;
	if (! is_file($path)) {
		fwrite(STDERR, "Missing file: {$relative_path}\n");
		exit(1);
	}

	$contents = file_get_contents($path);
	if (false === $contents) {
		fwrite(STDERR, "Unable to read file: {$relative_path}\n");
		exit(1);
	}

	return $contents;
}

/**
 * Records a failed assertion.
 *
 * @param bool     $condition Assertion result.
 * @param string   $message   Failure message.
 * @param string[] $failures  Failure collection.
 * @return void
 */
function hp_check_assert(bool $condition, string $message, array &$failures): void {
	if (! $condition) {
		$failures[] = $message;
	}
}

$failures = [];

$loader      = hp_check_read('inc/seo-schema.php');
$helpers     = hp_check_read('inc/helpers.php');
$seo_meta    = hp_check_read('inc/seo-meta.php');
$sitemap     = hp_check_read('inc/sitemap.php');
$dossier     = hp_check_read('inc/dossier.php');
$breadcrumbs = hp_check_read('inc/breadcrumbs.php');
$schema_helpers  = hp_check_read('inc/seo/schema-helpers.php');
$schema_site     = hp_check_read('inc/seo/schema-site.php');
$schema_content  = hp_check_read('inc/seo/schema-content.php');
$schema_archives = hp_check_read('inc/seo/schema-archives.php');

foreach (
	[
		'inc/seo/schema-helpers.php'  => $schema_helpers,
		'inc/seo/schema-site.php'     => $schema_site,
		'inc/seo/schema-content.php'  => $schema_content,
		'inc/seo/schema-archives.php' => $schema_archives,
	] as $file => $contents
) {
	hp_check_assert(
		false !== strpos($contents, "defined( 'ABSPATH' ) || exit;"),
		"Schema module lacks ABSPATH guard: {$file}",
		$failures
	);
}

foreach (
	[
		"schema-helpers.php",
		"schema-site.php",
		"schema-content.php",
		"schema-archives.php",
	] as $module
) {
	hp_check_assert(
		false !== strpos($loader, "require_once \$hp_schema_dir . '/{$module}';"),
		"Schema loader does not require {$module}",
		$failures
	);
}

hp_check_assert(
	false !== strpos($helpers, 'function hp_normalize_public_url('),
	'Shared URL normalizer must live in inc/helpers.php',
	$failures
);
hp_check_assert(
	false === strpos($seo_meta, 'function hp_normalize_public_url('),
	'SEO meta module must not own the shared URL normalizer',
	$failures
);

hp_check_assert(
	false !== strpos($breadcrumbs, "is_singular( 'dossier' )"),
	'Dossier singles need BreadcrumbList coverage',
	$failures
);
hp_check_assert(
	false !== strpos($breadcrumbs, "is_post_type_archive( 'dossier' )"),
	'Dossier archive needs BreadcrumbList coverage',
	$failures
);

hp_check_assert(
	false !== strpos($schema_content, 'hp_schema_iso_datetime_from_meta_date( $stand'),
	'Dossier schema must use the guarded ISO date helper',
	$failures
);
hp_check_assert(
	false === strpos($schema_content, "date( 'c', strtotime( \$stand ) )"),
	'Dossier schema must not convert invalid dates to 1970',
	$failures
);
hp_check_assert(
	false !== strpos($dossier, 'function hp_dossier_parse_stand_timestamp('),
	'Dossier citation dates need a guarded timestamp parser',
	$failures
);
hp_check_assert(
	false === strpos($dossier, "date_i18n( 'Y', strtotime( \$stand ) )"),
	'Dossier citations must not convert invalid dates to 1970',
	$failures
);
hp_check_assert(
	false !== strpos($sitemap, 'hp_dossier_get_disabled_ids()'),
	'Disabled dossiers must be excluded from the core sitemap',
	$failures
);

hp_check_assert(
	false !== strpos($schema_helpers, 'is_wp_error( $link )'),
	'Topic schema URL helpers must guard WP_Error links',
	$failures
);

foreach (['is_search()', 'is_404()', 'is_attachment()', 'is_paged()'] as $condition) {
	hp_check_assert(
		false !== strpos($seo_meta, $condition),
		"Canonical guard must include {$condition}",
		$failures
	);
}

if ($failures) {
	fwrite(STDERR, "SEO architecture check failed:\n");
	foreach ($failures as $failure) {
		fwrite(STDERR, "- {$failure}\n");
	}
	exit(1);
}

echo "SEO architecture check OK\n";
