<?php
/**
 * Einmaliges Wartungs-Skript: leert den OPcache des laufenden PHP-FPM-Prozesses.
 *
 * Hintergrund: Neue Theme-Dateien werden deployt, aber OPcache liefert die alte
 * kompilierte Version weiter aus (vermutlich opcache.validate_timestamps=0).
 * Dieser NEUE Dateipfad ist OPcache unbekannt und wird deshalb frisch ausgeführt,
 * sodass opcache_reset() den gesamten Cache leeren kann.
 *
 * Aufruf (einmalig):
 *   https://hasimuener.org/wp-content/themes/generatepress-child/opcache-reset.php?key=hu-opcache-9f3a
 *
 * Nach erfolgreichem Lauf bitte wieder entfernen.
 */

header( 'Content-Type: text/plain; charset=utf-8' );

if ( ( $_GET['key'] ?? '' ) !== 'hu-opcache-9f3a' ) {
	http_response_code( 403 );
	exit( "forbidden\n" );
}

echo 'PHP ' . PHP_VERSION . ' / SAPI ' . PHP_SAPI . "\n";

if ( function_exists( 'opcache_get_status' ) ) {
	$status = @opcache_get_status( false );
	echo 'OPcache aktiv: ' . ( ! empty( $status['opcache_enabled'] ) ? 'ja' : 'nein' ) . "\n";
}

if ( function_exists( 'opcache_reset' ) ) {
	$ok = opcache_reset();
	echo $ok
		? "OPcache wurde geleert. ✔  Bitte jetzt den Essay neu laden und diese Datei löschen.\n"
		: "opcache_reset() lief, lieferte aber false (evtl. CLI-Kontext oder bereits leer).\n";
} else {
	echo "opcache_reset() ist nicht verfuegbar — OPcache ist auf diesem Server deaktiviert.\n";
	echo "Dann liegt es NICHT am OPcache, sondern an einer alten Datei auf der Platte.\n";
}
