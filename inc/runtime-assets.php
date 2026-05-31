<?php
/**
 * Runtime-Assets — Hasimuener Journal
 *
 * Zentrale Helfer für Asset-Versionierung und Script-Strategie.
 *
 * Hintergrund: Jedes Asset braucht einen Cache-Bust-Versionsstring.
 * Vor 5.8.0 war die `filemtime()`-Logik in `enqueue.php` für jedes
 * Asset einzeln dupliziert. Dieses Modul bündelt das Muster in einer
 * Funktion und ergänzt einen Helfer für die native WordPress-
 * Defer-Strategie (WP 6.3+, `wp_enqueue_script( …, [ 'strategy' => … ] )`).
 *
 * Architektur-Prinzip: Reine Funktionen, keine Hooks. Wird vor
 * `enqueue.php` geladen (siehe manifest.php), damit die Helfer dort
 * verfügbar sind.
 *
 * @package Hasimuener_Journal
 * @since   5.8.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Liefert einen Cache-Bust-Versionsstring für ein Theme-Asset.
 *
 * Nutzt `filemtime()` des Assets relativ zum Stylesheet-Verzeichnis —
 * so ändert sich die Version automatisch bei jeder Dateiänderung und
 * der Browser-Cache wird zielgenau invalidiert. Fehlt die Datei, fällt
 * der Helfer auf die Theme-Version zurück.
 *
 * @param string $relative_path Pfad relativ zum Child-Theme-Root,
 *                              z. B. 'assets/js/nav.js'.
 * @return string Versionsstring (filemtime oder Theme-Version).
 */
function hp_asset_version( string $relative_path ): string {
	$relative_path = ltrim( $relative_path, '/' );
	$absolute_path = get_stylesheet_directory() . '/' . $relative_path;

	if ( is_readable( $absolute_path ) ) {
		$mtime = filemtime( $absolute_path );
		if ( false !== $mtime ) {
			return (string) $mtime;
		}
	}

	return (string) wp_get_theme()->get( 'Version' );
}

/**
 * Reiht ein Theme-Script mit Defer-Strategie und filemtime-Version ein.
 *
 * Dünner Wrapper um `wp_enqueue_script()`, der die zwei wiederkehrenden
 * Entscheidungen kapselt: korrekte Versionierung (siehe
 * {@see hp_asset_version()}) und nicht-render-blockierendes Laden via
 * `defer` im Footer. Für Scripts, die erst nach dem Parsen gebraucht
 * werden (Navigation, Tooltips, Single-Interaktionen) ist das der
 * Standardfall.
 *
 * @param string               $handle        Eindeutiges Script-Handle.
 * @param string               $relative_path Pfad relativ zum Child-Theme-Root.
 * @param array<int,string>    $deps          Abhängige Handles.
 * @param string               $strategy      'defer' (Standard) oder 'async'.
 * @return void
 */
function hp_enqueue_deferred_script(
	string $handle,
	string $relative_path,
	array $deps = [],
	string $strategy = 'defer'
): void {
	$relative_path = ltrim( $relative_path, '/' );
	$src           = get_stylesheet_directory_uri() . '/' . $relative_path;

	wp_enqueue_script(
		$handle,
		$src,
		$deps,
		hp_asset_version( $relative_path ),
		[
			'strategy'  => in_array( $strategy, [ 'defer', 'async' ], true ) ? $strategy : 'defer',
			'in_footer' => true,
		]
	);
}
