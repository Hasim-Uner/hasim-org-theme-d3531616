<?php
/**
 * Dossier — Kuratierte Themenbündel
 *
 * Wissensplattform Phase 3:
 * Ein Dossier ist ein redaktionell zusammengestelltes Bündel
 * aus Intro, Leseplan (Essays + Notizen in fester Reihenfolge),
 * Begriffsapparat (verlinkte Begriffe) und Quellen — der
 * thematische Gegen-Einstieg zum chronologischen Stream.
 *
 * Design-Prinzip: Reihenfolge ist Teil des Wissens.
 * Anders als ein Tag oder ein Themenfeld ist ein Dossier
 * gerichtet — es hat einen Anfang und ein Ende.
 *
 * @package Hasimuener_Journal
 * @since   5.4.0
 */

defined( 'ABSPATH' ) || exit;

/* =========================================
   1. CPT REGISTRIERUNG
   ========================================= */

/**
 * Registriert den Custom Post Type "dossier".
 *
 * Slug /dossiers/ — Plural, weil die Archive-URL gemeint ist.
 * Single-URLs sind /dossiers/<slug>/.
 */
function hp_register_dossier_cpt(): void {

	register_post_type( 'dossier', [
		'labels' => [
			'name'               => 'Dossiers',
			'singular_name'      => 'Dossier',
			'add_new'            => 'Neues Dossier',
			'add_new_item'       => 'Neues Dossier erstellen',
			'edit_item'          => 'Dossier bearbeiten',
			'view_item'          => 'Dossier ansehen',
			'all_items'          => 'Alle Dossiers',
			'search_items'       => 'Dossiers durchsuchen',
			'not_found'          => 'Keine Dossiers gefunden.',
			'not_found_in_trash' => 'Keine Dossiers im Papierkorb.',
		],
		'public'        => true,
		'has_archive'   => true,
		'rewrite'       => [ 'slug' => 'dossiers', 'with_front' => false ],
		'menu_icon'     => 'dashicons-portfolio',
		'menu_position' => 8,
		'supports'      => [ 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'custom-fields' ],
		'taxonomies'    => [ 'topic' ],
		'show_in_rest'  => true,
		'description'   => 'Kuratierte Themenbündel: Intro, Leseplan, Begriffsapparat, Quellen.',
	] );
}
add_action( 'init', 'hp_register_dossier_cpt' );

/**
 * Einmaliger Rewrite-Flush nach CPT-Registrierung.
 *
 * Wird einmalig nach dem ersten init-Hook ausgeführt, damit
 * /dossiers/ und /dossiers/<slug>/ ohne 404 erreichbar sind.
 * Option-Flag verhindert Mehrfachausführung.
 */
function hp_dossier_maybe_flush_rewrites(): void {
	if ( get_option( 'hp_dossier_rewrites_flushed_v1' ) ) {
		return;
	}
	flush_rewrite_rules( false );
	update_option( 'hp_dossier_rewrites_flushed_v1', true, false );
}
add_action( 'init', 'hp_dossier_maybe_flush_rewrites', 99 );

/* -----------------------------------------
   Taxonomie "topic" auch für Dossier
   ----------------------------------------- */

/**
 * Hängt den dossier-CPT an die bestehende topic-Taxonomie an.
 *
 * Wird auf init mit Priorität 12 registriert — nach
 * hp_register_taxonomies() (Default-Priorität 10), aber vor
 * der Standard-Init-Phase. So bleibt taxonomies.php Single Source
 * of Truth für topic, und das Dossier-Modul ergänzt nur die
 * Objekt-Zuordnung.
 */
function hp_attach_dossier_to_topic(): void {
	register_taxonomy_for_object_type( 'topic', 'dossier' );
}
add_action( 'init', 'hp_attach_dossier_to_topic', 12 );

/* =========================================
   2. META-FELDER
   ========================================= */

/**
 * Registriert die Dossier-spezifischen Meta-Felder.
 *
 * - _hp_dossier_intro:         Lede-Text (1–3 Sätze, im Hero)
 * - _hp_dossier_leseplan:      Komma-separierte Essay/Notiz-IDs in
 *                              kuratierter Lese-Reihenfolge
 * - _hp_dossier_begriffe:      Komma-separierte Glossar-IDs (Begriffsapparat)
 * - _hp_dossier_quellen:       Freier Text, eine Quelle pro Zeile
 * - _hp_dossier_kuratiert_von: Kurator-Name (z. B. "Haşim Üner")
 * - _hp_dossier_version:       Versionstring ("1.0", "1.3")
 * - _hp_dossier_stand:         ISO-Datum YYYY-MM-DD
 */
function hp_register_dossier_meta(): void {

	$meta_args = [
		'object_subtype' => 'dossier',
		'show_in_rest'   => true,
		'single'         => true,
		'type'           => 'string',
		'auth_callback'  => function () {
			return current_user_can( 'edit_posts' );
		},
	];

	register_post_meta( 'dossier', '_hp_dossier_intro', array_merge( $meta_args, [
		'sanitize_callback' => 'sanitize_textarea_field',
		'default'           => '',
	] ) );

	register_post_meta( 'dossier', '_hp_dossier_leseplan', array_merge( $meta_args, [
		'sanitize_callback' => 'sanitize_text_field',
		'default'           => '',
	] ) );

	register_post_meta( 'dossier', '_hp_dossier_begriffe', array_merge( $meta_args, [
		'sanitize_callback' => 'sanitize_text_field',
		'default'           => '',
	] ) );

	register_post_meta( 'dossier', '_hp_dossier_quellen', array_merge( $meta_args, [
		'sanitize_callback' => 'sanitize_textarea_field',
		'default'           => '',
	] ) );

	register_post_meta( 'dossier', '_hp_dossier_kuratiert_von', array_merge( $meta_args, [
		'sanitize_callback' => 'sanitize_text_field',
		'default'           => '',
	] ) );

	register_post_meta( 'dossier', '_hp_dossier_version', array_merge( $meta_args, [
		'sanitize_callback' => 'sanitize_text_field',
		'default'           => '',
	] ) );

	register_post_meta( 'dossier', '_hp_dossier_stand', array_merge( $meta_args, [
		'sanitize_callback' => 'sanitize_text_field',
		'default'           => '',
	] ) );
}
add_action( 'init', 'hp_register_dossier_meta' );

/* =========================================
   3. GUTENBERG SIDEBAR-PANELS
   ========================================= */

/**
 * Inline-JS Panels im Block-Editor für Dossier-Felder.
 * Drei Panels: Intro/Kuratiert-Von, Leseplan/Begriffe, Quellen/Stand.
 */
function hp_dossier_editor_panel(): void {

	$screen = get_current_screen();
	if ( ! $screen || 'dossier' !== $screen->post_type ) {
		return;
	}

	wp_add_inline_script( 'wp-edit-post', "
		( function() {
			var el          = wp.element.createElement;
			var Fragment    = wp.element.Fragment;
			var PluginPanel = wp.editPost.PluginDocumentSettingPanel;
			var TextArea    = wp.components.TextareaControl;
			var TextControl = wp.components.TextControl;
			var useSelect   = wp.data.useSelect;
			var useDispatch = wp.data.useDispatch;

			var DossierPanel = function() {
				var meta = useSelect( function( select ) {
					return select( 'core/editor' ).getEditedPostAttribute( 'meta' ) || {};
				} );
				var editPost = useDispatch( 'core/editor' ).editPost;

				function setMeta( key, value ) {
					var update = {};
					update[ key ] = value;
					editPost( { meta: update } );
				}

				return el( Fragment, null,
					el( PluginPanel, {
						name:  'hp-dossier-intro-panel',
						title: 'Dossier-Intro',
						icon:  'portfolio',
					},
						el( TextArea, {
							label: 'Intro / Lede',
							help:  '1–3 Sätze. Erscheint im Hero und in der Dossier-Übersicht.',
							value: meta._hp_dossier_intro || '',
							onChange: function( v ) { setMeta( '_hp_dossier_intro', v ); },
							rows: 4,
						}),
						el( TextControl, {
							label: 'Kuratiert von',
							help:  'Kurator-Name, z. B. „Haşim Üner“.',
							value: meta._hp_dossier_kuratiert_von || '',
							onChange: function( v ) { setMeta( '_hp_dossier_kuratiert_von', v ); },
						})
					),
					el( PluginPanel, {
						name:  'hp-dossier-inhalte-panel',
						title: 'Leseplan & Begriffe',
						icon:  'list-view',
					},
						el( TextArea, {
							label: 'Leseplan (Beitrags-IDs)',
							help:  'Komma-getrennte Essay/Notiz-IDs in der gewünschten Lese-Reihenfolge.',
							value: meta._hp_dossier_leseplan || '',
							onChange: function( v ) { setMeta( '_hp_dossier_leseplan', v ); },
							rows: 3,
						}),
						el( TextArea, {
							label: 'Begriffsapparat (Begriff-IDs)',
							help:  'Komma-getrennte Glossar-IDs der Begriffe, die in diesem Dossier zentral sind.',
							value: meta._hp_dossier_begriffe || '',
							onChange: function( v ) { setMeta( '_hp_dossier_begriffe', v ); },
							rows: 3,
						})
					),
					el( PluginPanel, {
						name:  'hp-dossier-meta-panel',
						title: 'Quellen, Stand & Version',
						icon:  'calendar-alt',
					},
						el( TextArea, {
							label: 'Quellen',
							help:  'Eine Quelle pro Zeile. Freier Text mit optionalen URLs.',
							value: meta._hp_dossier_quellen || '',
							onChange: function( v ) { setMeta( '_hp_dossier_quellen', v ); },
							rows: 5,
						}),
						el( TextControl, {
							label: 'Version',
							help:  'Semantischer String, z. B. „1.0“.',
							value: meta._hp_dossier_version || '',
							onChange: function( v ) { setMeta( '_hp_dossier_version', v ); },
						}),
						el( TextControl, {
							label: 'Stand vom',
							help:  'ISO-Datum YYYY-MM-DD.',
							value: meta._hp_dossier_stand || '',
							onChange: function( v ) { setMeta( '_hp_dossier_stand', v ); },
						})
					)
				);
			};

			wp.plugins.registerPlugin( 'hp-dossier-panel', {
				render: DossierPanel,
				icon:   'portfolio',
			} );
		} )();
	" );
}
add_action( 'enqueue_block_editor_assets', 'hp_dossier_editor_panel' );

/* =========================================
   4. HILFSFUNKTIONEN (Template-Layer)
   ========================================= */

/**
 * Parst eine komma-separierte ID-Liste in ein Array von Post-IDs.
 *
 * @param string $raw Komma-separierte IDs aus Meta.
 * @return int[]
 */
function hp_dossier_parse_ids( string $raw ): array {
	if ( '' === trim( $raw ) ) {
		return [];
	}
	return array_values( array_filter( array_map( 'intval', array_map( 'trim', explode( ',', $raw ) ) ) ) );
}

/**
 * Liefert die im Leseplan referenzierten Beiträge in
 * der vom Kurator definierten Reihenfolge.
 *
 * @param int $dossier_id Dossier-Post-ID.
 * @return WP_Post[] Liste von Posts (essay, note) in Lese-Reihenfolge.
 */
function hp_dossier_get_leseplan( int $dossier_id ): array {
	$ids = hp_dossier_parse_ids( (string) get_post_meta( $dossier_id, '_hp_dossier_leseplan', true ) );

	if ( ! $ids ) {
		return [];
	}

	$posts = get_posts( [
		'post_type'      => [ 'essay', 'note' ],
		'post__in'       => $ids,
		'orderby'        => 'post__in',
		'posts_per_page' => count( $ids ),
		'post_status'    => 'publish',
	] );

	return $posts;
}

/**
 * Liefert die im Begriffsapparat referenzierten Glossar-Einträge.
 *
 * @param int $dossier_id Dossier-Post-ID.
 * @return WP_Post[] Liste von Glossar-Posts in Kurator-Reihenfolge.
 */
function hp_dossier_get_begriffe( int $dossier_id ): array {
	$ids = hp_dossier_parse_ids( (string) get_post_meta( $dossier_id, '_hp_dossier_begriffe', true ) );

	if ( ! $ids ) {
		return [];
	}

	$posts = get_posts( [
		'post_type'      => 'glossar',
		'post__in'       => $ids,
		'orderby'        => 'post__in',
		'posts_per_page' => count( $ids ),
		'post_status'    => 'publish',
	] );

	return $posts;
}
