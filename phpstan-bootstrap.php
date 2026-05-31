<?php
/**
 * PHPStan-Bootstrap — WordPress-Laufzeitkonstanten.
 *
 * Die WordPress-Stubs definieren Funktionen und Klassen, aber nicht alle
 * Laufzeitkonstanten. Diese werden hier deklariert, damit PHPStan keine
 * „Constant not found"-False-Positives meldet. Wird nur von PHPStan
 * geladen, niemals zur Laufzeit (WordPress definiert die Werte selbst).
 *
 * @package Hasimuener_Journal
 */

declare( strict_types=1 );

// Zeit-Konstanten (wp-includes/default-constants.php).
defined( 'MINUTE_IN_SECONDS' ) || define( 'MINUTE_IN_SECONDS', 60 );
defined( 'HOUR_IN_SECONDS' )   || define( 'HOUR_IN_SECONDS', 60 * MINUTE_IN_SECONDS );
defined( 'DAY_IN_SECONDS' )    || define( 'DAY_IN_SECONDS', 24 * HOUR_IN_SECONDS );
defined( 'WEEK_IN_SECONDS' )   || define( 'WEEK_IN_SECONDS', 7 * DAY_IN_SECONDS );
defined( 'MONTH_IN_SECONDS' )  || define( 'MONTH_IN_SECONDS', 30 * DAY_IN_SECONDS );
defined( 'YEAR_IN_SECONDS' )   || define( 'YEAR_IN_SECONDS', 365 * DAY_IN_SECONDS );

// wpdb-Ausgabetypen (wp-includes/wp-db.php / class-wpdb.php).
defined( 'OBJECT' )   || define( 'OBJECT', 'OBJECT' );
defined( 'OBJECT_K' ) || define( 'OBJECT_K', 'OBJECT_K' );
defined( 'ARRAY_A' )  || define( 'ARRAY_A', 'ARRAY_A' );
defined( 'ARRAY_N' )  || define( 'ARRAY_N', 'ARRAY_N' );
