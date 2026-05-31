<?php
/**
 * Feature-Flags — Hasimuener Journal
 *
 * Leichtgewichtiges Flag-System für kontrollierte Rollouts: neue
 * Schemata, experimentelle Templates, Instrumentierung oder
 * Runtime-Verhalten lassen sich an einer Stelle ein-/ausschalten,
 * ohne Code zu entfernen.
 *
 * Quellen (in Prioritätsreihenfolge, höchste zuerst):
 *   1. Filter `hp_feature_flag_{flag}` — Override pro Flag.
 *   2. Filter `hp_feature_flags` — Override der gesamten Map.
 *   3. Konstante HP_FEATURE_FLAGS (Array) aus wp-config.php.
 *   4. Standardwerte aus diesem Modul.
 *
 * Nutzung:
 *   if ( hp_feature_enabled( 'content_instrumentation' ) ) { … }
 *
 * @package Hasimuener_Journal
 * @since   5.8.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Standard-Feature-Flags des Themes.
 *
 * @return array<string,bool>
 */
function hp_feature_flag_defaults(): array {
	return [
		// Semantische data-Attribute für Content-/CTA-Interaktionen in
		// Templates (siehe docs). Standardmäßig aus, bis ein Consent-
		// fähiger Analytics-Layer angebunden ist.
		'content_instrumentation' => false,

		// Dynamische /llms.txt-Route für KI-Crawler (inc/llms-txt.php).
		'llms_txt' => true,

		// User-Sitemap aus dem WP-Core-Sitemap-Index entfernen
		// (Single-Author-Setup, kein Ranking-Wert).
		'sitemap_drop_users' => true,
	];
}

/**
 * Liefert die aufgelöste Flag-Map (Defaults + Konstante + Filter).
 *
 * @return array<string,bool>
 */
function hp_feature_flags(): array {
	$flags = hp_feature_flag_defaults();

	if ( defined( 'HP_FEATURE_FLAGS' ) && is_array( HP_FEATURE_FLAGS ) ) {
		foreach ( HP_FEATURE_FLAGS as $key => $value ) {
			if ( is_string( $key ) ) {
				$flags[ $key ] = (bool) $value;
			}
		}
	}

	/**
	 * Filtert die gesamte Feature-Flag-Map.
	 *
	 * @param array<string,bool> $flags
	 */
	$flags = apply_filters( 'hp_feature_flags', $flags );

	return is_array( $flags ) ? $flags : hp_feature_flag_defaults();
}

/**
 * Prüft, ob ein Feature aktiv ist.
 *
 * @param string $flag    Flag-Schlüssel.
 * @param bool   $default Rückgabewert, falls das Flag unbekannt ist.
 * @return bool
 */
function hp_feature_enabled( string $flag, bool $default = false ): bool {
	$flags = hp_feature_flags();
	$value = array_key_exists( $flag, $flags ) ? (bool) $flags[ $flag ] : $default;

	/**
	 * Filtert ein einzelnes Feature-Flag (höchste Priorität).
	 *
	 * @param bool   $value
	 * @param string $flag
	 */
	return (bool) apply_filters( "hp_feature_flag_{$flag}", $value, $flag );
}
