#!/usr/bin/env php
<?php
/**
 * Generate lightweight WordPress architecture reference docs.
 *
 * Scans PHP files for WordPress hook registrations and REST route
 * registrations, then writes:
 * - docs/HOOKS.md
 * - docs/REST_ROUTES.md
 *
 * This is intentionally regex-based and dependency-free. It is a reference
 * generator, not a PHP parser.
 */

declare(strict_types=1);

$root = dirname(__DIR__);

/**
 * @return array<int,string>
 */
function hp_doc_php_files(string $root): array {
	$skip_dirs = [
		$root . '/vendor',
		$root . '/_build-d3/node_modules',
		$root . '/.git',
	];

	$files = [];
	$iterator = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
	);

	foreach ($iterator as $file) {
		if (!$file instanceof SplFileInfo || 'php' !== strtolower($file->getExtension())) {
			continue;
		}

		$path = $file->getPathname();
		foreach ($skip_dirs as $skip_dir) {
			if (0 === strpos($path, $skip_dir . DIRECTORY_SEPARATOR)) {
				continue 2;
			}
		}

		$files[] = $path;
	}

	sort($files);
	return $files;
}

function hp_doc_relative(string $root, string $path): string {
	return ltrim(str_replace($root, '', $path), DIRECTORY_SEPARATOR);
}

function hp_doc_extract_callback(string $raw): string {
	$raw = trim($raw);
	$raw = preg_replace('/\s+/', ' ', $raw) ?? $raw;

	if (preg_match('/^[\'"]([^\'"]+)[\'"]$/', $raw, $m)) {
		return $m[1];
	}

	if (0 === strpos($raw, 'function') || 0 === strpos($raw, 'static function')) {
		return 'closure';
	}

	if (false !== strpos($raw, '__return_')) {
		return trim($raw, " \t\n\r\0\x0B,");
	}

	return trim($raw, " \t\n\r\0\x0B,");
}

function hp_doc_unquote_arg(string $raw): string {
	$raw = trim($raw);

	if (preg_match('/^[\'"]([^\'"]+)[\'"]$/', $raw, $m)) {
		return $m[1];
	}

	return $raw;
}

/**
 * Split a function call argument list at top-level commas.
 *
 * @return array<int,string>
 */
function hp_doc_split_args(string $args): array {
	$parts = [];
	$buffer = '';
	$depth = 0;
	$quote = null;
	$escape = false;
	$length = strlen($args);

	for ($i = 0; $i < $length; $i++) {
		$char = $args[$i];

		if (null !== $quote) {
			$buffer .= $char;

			if ($escape) {
				$escape = false;
				continue;
			}

			if ('\\' === $char) {
				$escape = true;
				continue;
			}

			if ($char === $quote) {
				$quote = null;
			}

			continue;
		}

		if ('\'' === $char || '"' === $char) {
			$quote = $char;
			$buffer .= $char;
			continue;
		}

		if (in_array($char, ['(', '[', '{'], true)) {
			$depth++;
			$buffer .= $char;
			continue;
		}

		if (in_array($char, [')', ']', '}'], true)) {
			$depth = max(0, $depth - 1);
			$buffer .= $char;
			continue;
		}

		if (',' === $char && 0 === $depth) {
			$parts[] = trim($buffer);
			$buffer = '';
			continue;
		}

		$buffer .= $char;
	}

	if ('' !== trim($buffer)) {
		$parts[] = trim($buffer);
	}

	return $parts;
}

$hooks = [];
$routes = [];

foreach (hp_doc_php_files($root) as $path) {
	$relative = hp_doc_relative($root, $path);

	if ('inc/contact-local.php' === $relative) {
		continue;
	}

	$lines = file($path, FILE_IGNORE_NEW_LINES);
	if (false === $lines) {
		continue;
	}

	$content = implode("\n", $lines);
	if (preg_match_all('/add_(action|filter)\s*\((.*?)\)\s*;/s', $content, $hook_matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
		foreach ($hook_matches as $match) {
			$args = hp_doc_split_args($match[2][0]);

			if (count($args) < 2) {
				continue;
			}

			$line = substr_count(substr($content, 0, $match[0][1]), "\n") + 1;
			$hooks[] = [
				'type' => 'add_' . $match[1][0],
				'hook' => hp_doc_unquote_arg($args[0]),
				'callback' => hp_doc_extract_callback($args[1]),
				'priority' => isset($args[2]) ? hp_doc_unquote_arg($args[2]) : '10',
				'accepted_args' => isset($args[3]) ? hp_doc_unquote_arg($args[3]) : '1',
				'file' => $relative,
				'line' => $line,
			];
		}
	}

	if (preg_match_all('/register_rest_route\s*\(\s*([\'"])([^\'"]+)\1\s*,\s*([\'"])([^\'"]+)\3\s*,\s*(\[.*?\])\s*\)/s', $content, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
		foreach ($matches as $match) {
			$snippet = $match[5][0];
			$line = substr_count(substr($content, 0, $match[0][1]), "\n") + 1;
			$methods = 'UNKNOWN';
			$callback = 'unknown';
			$permission = 'unknown';

			if (preg_match('/[\'"]methods[\'"]\s*=>\s*([\'"])([^\'"]+)\1/', $snippet, $mm)) {
				$methods = $mm[2];
			}
			if (preg_match('/[\'"]callback[\'"]\s*=>\s*([\'"])([^\'"]+)\1/', $snippet, $cm)) {
				$callback = $cm[2];
			}
			if (preg_match('/[\'"]permission_callback[\'"]\s*=>\s*([\'"])([^\'"]+)\1/', $snippet, $pm)) {
				$permission = $pm[2];
			}

			$routes[] = [
				'namespace' => $match[2][0],
				'route' => $match[4][0],
				'methods' => $methods,
				'callback' => $callback,
				'permission' => $permission,
				'file' => $relative,
				'line' => $line,
			];
		}
	}
}

usort($hooks, static function (array $a, array $b): int {
	return [$a['hook'], $a['file'], $a['line']] <=> [$b['hook'], $b['file'], $b['line']];
});

usort($routes, static function (array $a, array $b): int {
	return [$a['namespace'], $a['route']] <=> [$b['namespace'], $b['route']];
});

$hook_doc = [];
$hook_doc[] = '# Hooks';
$hook_doc[] = '';
$hook_doc[] = 'Generated by `php scripts/generate-wp-docs.php`.';
$hook_doc[] = '';
$hook_doc[] = '| Type | Hook | Callback | Priority | Args | File |';
$hook_doc[] = '|---|---|---|---:|---:|---|';

foreach ($hooks as $hook) {
	$file = $hook['file'] . ':' . $hook['line'];
	$hook_doc[] = sprintf(
		'| `%s` | `%s` | `%s` | %s | %s | `%s` |',
		$hook['type'],
		str_replace('|', '\\|', $hook['hook']),
		str_replace('|', '\\|', $hook['callback']),
		$hook['priority'],
		$hook['accepted_args'],
		$file
	);
}

$route_doc = [];
$route_doc[] = '# REST Routes';
$route_doc[] = '';
$route_doc[] = 'Generated by `php scripts/generate-wp-docs.php`.';
$route_doc[] = '';
$route_doc[] = '| Method | Route | Callback | Permission | File |';
$route_doc[] = '|---|---|---|---|---|';

foreach ($routes as $route) {
	$file = $route['file'] . ':' . $route['line'];
	$route_doc[] = sprintf(
		'| `%s` | `%s/%s` | `%s` | `%s` | `%s` |',
		$route['methods'],
		rtrim($route['namespace'], '/'),
		ltrim($route['route'], '/'),
		$route['callback'],
		$route['permission'],
		$file
	);
}

file_put_contents($root . '/docs/HOOKS.md', implode("\n", $hook_doc) . "\n");
file_put_contents($root . '/docs/REST_ROUTES.md', implode("\n", $route_doc) . "\n");

printf("Generated %d hooks and %d REST routes.\n", count($hooks), count($routes));
