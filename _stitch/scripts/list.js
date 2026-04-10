#!/usr/bin/env node
/**
 * Projekte und Screens auflisten.
 *
 * Verwendung:
 *   npm run list                          — Alle Projekte anzeigen
 *   npm run list -- --project ID          — Screens eines Projekts anzeigen
 */
import { stitch, PROJECT_ID } from './config.js';

async function main() {
	const args = process.argv.slice( 2 );
	let projectId = PROJECT_ID;

	for ( let i = 0; i < args.length; i++ ) {
		if ( args[ i ] === '--project' ) {
			projectId = args[ ++i ];
		}
	}

	if ( projectId ) {
		// Screens eines Projekts anzeigen
		console.log( `\n=== Screens in Projekt: ${ projectId } ===\n` );
		const project = stitch.project( projectId );
		const screens = await project.screens();

		if ( screens.length === 0 ) {
			console.log( 'Keine Screens vorhanden.' );
			return;
		}

		for ( const screen of screens ) {
			console.log( `  ID: ${ screen.id || screen.screenId }` );
		}
		console.log( `\nGesamt: ${ screens.length } Screen(s)` );
	} else {
		// Alle Projekte anzeigen
		console.log( '\n=== Stitch Projekte ===\n' );
		const projects = await stitch.projects();

		if ( projects.length === 0 ) {
			console.log( 'Keine Projekte vorhanden. Erstelle eins mit:' );
			console.log( '  npm run generate -- --prompt "Dein Design"' );
			return;
		}

		for ( const project of projects ) {
			const screens = await project.screens();
			console.log( `  ${ project.id || project.projectId } — ${ screens.length } Screen(s)` );
		}
		console.log( `\nGesamt: ${ projects.length } Projekt(e)` );
	}
}

main().catch( ( err ) => {
	console.error( 'Fehler:', err.message );
	process.exit( 1 );
} );
