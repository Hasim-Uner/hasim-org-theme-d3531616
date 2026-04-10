#!/usr/bin/env node
/**
 * Design generieren mit Google Stitch.
 *
 * Verwendung:
 *   npm run generate -- --prompt "Artikelseite im Editorial-Stil" [--device DESKTOP] [--project ID]
 *
 * Optionen:
 *   --prompt     Beschreibung des gewünschten Designs (Pflicht)
 *   --device     MOBILE | DESKTOP | TABLET | AGNOSTIC (Standard: DESKTOP)
 *   --project    Stitch-Projekt-ID (oder STITCH_PROJECT_ID aus .env)
 *   --variants   Anzahl Varianten generieren (1-5)
 *   --export     HTML und Screenshot direkt exportieren
 */
import { writeFile, mkdir } from 'node:fs/promises';
import { join, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';
import { stitch, PROJECT_ID } from './config.js';

const __dirname = dirname( fileURLToPath( import.meta.url ) );
const OUTPUT_DIR = join( __dirname, '..', 'output' );

function parseArgs() {
	const args = process.argv.slice( 2 );
	const opts = {
		prompt: null,
		device: 'DESKTOP',
		project: PROJECT_ID,
		variants: 0,
		export: false,
	};

	for ( let i = 0; i < args.length; i++ ) {
		switch ( args[ i ] ) {
			case '--prompt':
				opts.prompt = args[ ++i ];
				break;
			case '--device':
				opts.device = args[ ++i ];
				break;
			case '--project':
				opts.project = args[ ++i ];
				break;
			case '--variants':
				opts.variants = parseInt( args[ ++i ], 10 );
				break;
			case '--export':
				opts.export = true;
				break;
		}
	}

	if ( ! opts.prompt ) {
		console.error(
			'Verwendung: npm run generate -- --prompt "Beschreibung des Designs"\n\n' +
			'Beispiele:\n' +
			'  npm run generate -- --prompt "Startseite für politisches Magazin"\n' +
			'  npm run generate -- --prompt "Artikelseite mit Lesefortschritt" --device MOBILE\n' +
			'  npm run generate -- --prompt "Dashboard für Autoren" --variants 3 --export'
		);
		process.exit( 1 );
	}

	return opts;
}

async function getOrCreateProject( projectId ) {
	if ( projectId ) {
		console.log( `Projekt: ${ projectId }` );
		return stitch.project( projectId );
	}

	console.log( 'Kein Projekt angegeben — erstelle neues Projekt...' );
	const result = await stitch.callTool( 'create_project', {
		title: 'Hasim Design Studio',
	} );
	console.log( `Neues Projekt erstellt: ${ result.projectId || result.id }` );
	console.log( 'Tipp: Setze STITCH_PROJECT_ID in .env, um dieses Projekt wiederzuverwenden.' );
	return stitch.project( result.projectId || result.id );
}

async function exportScreen( screen, label ) {
	await mkdir( OUTPUT_DIR, { recursive: true } );

	const timestamp = new Date().toISOString().replace( /[:.]/g, '-' ).slice( 0, 19 );
	const prefix = `${ timestamp }_${ label }`;

	const htmlUrl = await screen.getHtml();
	const imageUrl = await screen.getImage();

	if ( htmlUrl ) {
		const res = await fetch( htmlUrl );
		const html = await res.text();
		const htmlPath = join( OUTPUT_DIR, `${ prefix }.html` );
		await writeFile( htmlPath, html, 'utf-8' );
		console.log( `  HTML: ${ htmlPath }` );
	}

	if ( imageUrl ) {
		const res = await fetch( imageUrl );
		const buffer = Buffer.from( await res.arrayBuffer() );
		const imgPath = join( OUTPUT_DIR, `${ prefix }.png` );
		await writeFile( imgPath, buffer );
		console.log( `  Screenshot: ${ imgPath }` );
	}
}

async function main() {
	const opts = parseArgs();

	console.log( '\n=== Hasim Stitch Design Generator ===' );
	console.log( `Prompt: "${ opts.prompt }"` );
	console.log( `Device: ${ opts.device }\n` );

	const project = await getOrCreateProject( opts.project );

	// Design generieren
	console.log( 'Generiere Design...' );
	const screen = await project.generate( opts.prompt, opts.device );
	console.log( `Screen erstellt: ${ screen.id || screen.screenId }` );

	// Optional: Varianten
	if ( opts.variants > 0 ) {
		console.log( `\nGeneriere ${ opts.variants } Varianten...` );
		const variants = await screen.variants(
			'Erstelle Variationen mit unterschiedlichem Layout und Farbschema',
			{
				variantCount: opts.variants,
				creativeRange: 'EXPLORE',
				aspects: [ 'LAYOUT', 'COLOR_SCHEME', 'TEXT_FONT' ],
			}
		);
		console.log( `${ variants.length } Varianten erstellt.` );

		if ( opts.export ) {
			for ( let i = 0; i < variants.length; i++ ) {
				console.log( `\nExportiere Variante ${ i + 1 }...` );
				await exportScreen( variants[ i ], `variante-${ i + 1 }` );
			}
		}
	}

	// Export
	if ( opts.export ) {
		console.log( '\nExportiere Hauptdesign...' );
		await exportScreen( screen, 'design' );
	}

	console.log( '\nFertig!' );
}

main().catch( ( err ) => {
	console.error( 'Fehler:', err.message );
	process.exit( 1 );
} );
