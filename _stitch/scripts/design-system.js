#!/usr/bin/env node
/**
 * Design-System für hasimuener.org in Stitch erstellen/anwenden.
 *
 * Erstellt ein Design-System basierend auf den bestehenden CSS-Custom-Properties
 * des Themes und wendet es auf Screens an.
 *
 * Verwendung:
 *   npm run design-system -- --project ID [--apply SCREEN_ID]
 */
import { stitch, PROJECT_ID } from './config.js';

/**
 * Hasim Design-Tokens — abgeleitet aus style.css :root
 */
const HASIM_DESIGN_SYSTEM = {
	name: 'Hasim Editorial',
	colors: {
		primary: '#b12a2a',
		primaryLight: '#c0392b',
		background: '#fdfdfd',
		surface: '#ffffff',
		text: '#1a1a1a',
		textSecondary: '#555555',
		textMuted: '#888888',
		border: '#e5e5e5',
		accent: '#b12a2a',
	},
	typography: {
		headingFont: 'Merriweather, Georgia, serif',
		bodyFont: '-apple-system, BlinkMacSystemFont, Segoe UI, Roboto, sans-serif',
		monoFont: 'SFMono-Regular, Consolas, Liberation Mono, monospace',
	},
	spacing: {
		base: '1rem',
		contentMaxWidth: '720px',
	},
};

function parseArgs() {
	const args = process.argv.slice( 2 );
	const opts = { project: PROJECT_ID, apply: null };

	for ( let i = 0; i < args.length; i++ ) {
		switch ( args[ i ] ) {
			case '--project':
				opts.project = args[ ++i ];
				break;
			case '--apply':
				opts.apply = args[ ++i ];
				break;
		}
	}

	if ( ! opts.project ) {
		console.error(
			'Verwendung: npm run design-system -- --project PROJEKT_ID [--apply SCREEN_ID]'
		);
		process.exit( 1 );
	}

	return opts;
}

async function main() {
	const opts = parseArgs();

	console.log( '\n=== Hasim Design-System ===' );
	console.log( `Projekt: ${ opts.project }\n` );

	const project = stitch.project( opts.project );

	// Design-System erstellen
	console.log( 'Erstelle Design-System "Hasim Editorial"...' );
	const designSystem = await project.createDesignSystem( HASIM_DESIGN_SYSTEM );
	console.log( `Design-System erstellt: ${ designSystem.id || designSystem.assetId }` );

	// Optional: Auf einen Screen anwenden
	if ( opts.apply ) {
		console.log( `\nWende Design-System auf Screen ${ opts.apply } an...` );
		const screen = await project.getScreen( opts.apply );
		const result = await designSystem.apply( [ screen ] );
		console.log( `Design-System angewendet auf ${ result.length } Screen(s).` );
	}

	console.log( '\nDesign-System Farben:' );
	for ( const [ key, value ] of Object.entries( HASIM_DESIGN_SYSTEM.colors ) ) {
		console.log( `  ${ key }: ${ value }` );
	}

	console.log( '\nFertig!' );
}

main().catch( ( err ) => {
	console.error( 'Fehler:', err.message );
	process.exit( 1 );
} );
