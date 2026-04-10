#!/usr/bin/env node
/**
 * Einen bestehenden Screen als HTML + Screenshot exportieren.
 *
 * Verwendung:
 *   npm run export -- --project ID --screen SCREEN_ID
 */
import { writeFile, mkdir } from 'node:fs/promises';
import { join, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';
import { stitch, PROJECT_ID } from './config.js';

const __dirname = dirname( fileURLToPath( import.meta.url ) );
const OUTPUT_DIR = join( __dirname, '..', 'output' );

function parseArgs() {
	const args = process.argv.slice( 2 );
	const opts = { project: PROJECT_ID, screen: null };

	for ( let i = 0; i < args.length; i++ ) {
		switch ( args[ i ] ) {
			case '--project':
				opts.project = args[ ++i ];
				break;
			case '--screen':
				opts.screen = args[ ++i ];
				break;
		}
	}

	if ( ! opts.project || ! opts.screen ) {
		console.error(
			'Verwendung: npm run export -- --project PROJEKT_ID --screen SCREEN_ID\n\n' +
			'Tipp: Nutze "npm run list" um IDs herauszufinden.'
		);
		process.exit( 1 );
	}

	return opts;
}

async function main() {
	const opts = parseArgs();

	console.log( '\n=== Stitch Screen Export ===' );
	console.log( `Projekt: ${ opts.project }` );
	console.log( `Screen:  ${ opts.screen }\n` );

	const project = stitch.project( opts.project );
	const screen = await project.getScreen( opts.screen );

	await mkdir( OUTPUT_DIR, { recursive: true } );

	const timestamp = new Date().toISOString().replace( /[:.]/g, '-' ).slice( 0, 19 );

	// HTML exportieren
	const htmlUrl = await screen.getHtml();
	if ( htmlUrl ) {
		const res = await fetch( htmlUrl );
		const html = await res.text();
		const htmlPath = join( OUTPUT_DIR, `${ timestamp }_export.html` );
		await writeFile( htmlPath, html, 'utf-8' );
		console.log( `HTML:       ${ htmlPath }` );
	}

	// Screenshot exportieren
	const imageUrl = await screen.getImage();
	if ( imageUrl ) {
		const res = await fetch( imageUrl );
		const buffer = Buffer.from( await res.arrayBuffer() );
		const imgPath = join( OUTPUT_DIR, `${ timestamp }_export.png` );
		await writeFile( imgPath, buffer );
		console.log( `Screenshot: ${ imgPath }` );
	}

	console.log( '\nExport abgeschlossen!' );
}

main().catch( ( err ) => {
	console.error( 'Fehler:', err.message );
	process.exit( 1 );
} );
