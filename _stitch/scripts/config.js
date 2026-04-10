/**
 * Gemeinsame Konfiguration für Stitch-Scripts.
 * Lädt .env und exportiert SDK-Instanzen.
 */
import 'dotenv/config';
import { stitch } from '@google/stitch-sdk';

if ( ! process.env.STITCH_API_KEY ) {
	console.error(
		'Fehler: STITCH_API_KEY nicht gesetzt.\n' +
		'Kopiere .env.example → .env und trage deinen API-Key ein.'
	);
	process.exit( 1 );
}

export { stitch };
export const PROJECT_ID = process.env.STITCH_PROJECT_ID || null;
