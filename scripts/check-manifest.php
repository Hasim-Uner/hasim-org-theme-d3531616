<?php
/**
 * Architektur-Check — Manifest-Integrität
 *
 * Standalone-CI-Skript (kein WordPress nötig). Validiert, dass die in
 * inc/manifest.php deklarierte Modul-Ladeliste konsistent ist:
 *
 *   1. Jeder Manifest-Eintrag ist ein nicht-leerer String.
 *   2. Jede referenzierte Datei existiert auf der Platte.
 *   3. Keine doppelten Einträge.
 *   4. Hinweis (nicht-fatal): top-level inc/*.php, die nicht im Manifest
 *      stehen und auch nicht von einem anderen Modul geladen werden.
 *
 * Exit-Code 0 = ok, 1 = Verletzung. Spiegelt die Laufzeit-Guards in
 * inc/bootstrap.php, fängt Fehler aber schon im PR statt erst zur
 * Laufzeit ab.
 *
 * @package Hasimuener_Journal
 */

declare( strict_types=1 );

// Manifest definiert `defined('ABSPATH')||exit;` — Konstante setzen,
// damit `require` das Array zurückgibt statt zu beenden.
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/../' );
}

$inc_dir       = realpath( __DIR__ . '/../inc' );
$manifest_path = $inc_dir . '/manifest.php';

if ( false === $inc_dir || ! is_file( $manifest_path ) ) {
	fwrite( STDERR, "FAIL: inc/manifest.php nicht gefunden.\n" );
	exit( 1 );
}

$manifest = require $manifest_path;

if ( ! is_array( $manifest ) ) {
	fwrite( STDERR, "FAIL: manifest.php muss ein Array zurückgeben.\n" );
	exit( 1 );
}

$errors = [];
$seen   = [];

foreach ( $manifest as $index => $entry ) {
	if ( ! is_string( $entry ) || '' === $entry ) {
		$errors[] = sprintf( 'Ungültiger Eintrag an Position %s (kein nicht-leerer String).', (string) $index );
		continue;
	}

	if ( isset( $seen[ $entry ] ) ) {
		$errors[] = sprintf( 'Doppelter Eintrag: %s', $entry );
	}
	$seen[ $entry ] = true;

	$module_path = $inc_dir . '/' . ltrim( $entry, '/' );
	if ( ! is_file( $module_path ) ) {
		$errors[] = sprintf( 'Modul fehlt auf der Platte: %s', $entry );
	}
}

// Nicht-fatale Diagnose: verwaiste top-level inc/*.php.
$top_level = glob( $inc_dir . '/*.php' ) ?: [];
$orphans   = [];
foreach ( $top_level as $file ) {
	$base = basename( $file );
	if ( 'manifest.php' === $base || 'bootstrap.php' === $base ) {
		continue;
	}
	if ( ! isset( $seen[ $base ] ) ) {
		$orphans[] = $base;
	}
}

if ( $errors ) {
	fwrite( STDERR, "Manifest-Check FEHLGESCHLAGEN:\n" );
	foreach ( $errors as $error ) {
		fwrite( STDERR, '  - ' . $error . "\n" );
	}
	exit( 1 );
}

printf( "Manifest-Check OK: %d Module, alle Dateien vorhanden.\n", count( $manifest ) );

if ( $orphans ) {
	fwrite( STDOUT, "Hinweis: top-level inc/*.php nicht im Manifest (ggf. von anderem Modul geladen):\n" );
	foreach ( $orphans as $orphan ) {
		fwrite( STDOUT, '  - ' . $orphan . "\n" );
	}
}

exit( 0 );
