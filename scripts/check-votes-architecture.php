<?php
/**
 * Vote architecture regression checks.
 *
 * Guards against reintroducing hot-path table checks or divergent nonce
 * concepts in the like/dislike voting surface.
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
function hp_votes_check_read(string $relative_path): string {
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
function hp_votes_check_assert(bool $condition, string $message, array &$failures): void {
	if (! $condition) {
		$failures[] = $message;
	}
}

$failures = [];
$votes    = hp_votes_check_read('inc/votes.php');
$api      = hp_votes_check_read('inc/votes-api.php');
$js       = hp_votes_check_read('assets/js/votes.js');

hp_votes_check_assert(
	false !== strpos($votes, 'const HP_VOTES_SCHEMA_VERSION'),
	'Votes need an explicit schema version constant',
	$failures
);
hp_votes_check_assert(
	false !== strpos($votes, "update_option( 'hp_votes_schema_version'"),
	'Vote table creation must persist the schema version',
	$failures
);
hp_votes_check_assert(
	false !== strpos($votes, 'function hp_votes_maybe_upgrade_schema('),
	'Votes need a version-gated migration function',
	$failures
);
hp_votes_check_assert(
	false === strpos($votes, 'SHOW TABLES'),
	'Votes must not run SHOW TABLES on the frontend hot path',
	$failures
);
hp_votes_check_assert(
	false === strpos($votes, "wp_create_nonce( 'hp_vote_' . \$post_id )"),
	'Vote button markup must not emit a second post-specific nonce',
	$failures
);
hp_votes_check_assert(
	false !== strpos($api, "wp_verify_nonce( \$nonce, 'hp_vote_nonce' )"),
	'Vote REST API must verify the shared vote nonce',
	$failures
);
hp_votes_check_assert(
	false !== strpos($js, 'nonce: config.nonce'),
	'Vote frontend must send the localized shared nonce',
	$failures
);

if ($failures) {
	fwrite(STDERR, "Vote architecture check failed:\n");
	foreach ($failures as $failure) {
		fwrite(STDERR, "- {$failure}\n");
	}
	exit(1);
}

echo "Vote architecture check OK\n";
