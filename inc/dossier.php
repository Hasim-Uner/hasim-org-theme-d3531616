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

/**
 * Reverse-Lookup: liefert alle Dossiers, in deren
 * Begriffsapparat der gegebene Glossar-Eintrag steht.
 *
 * Wird auf single-glossar.php verwendet, um die
 * Klick-Kette Glossar → Dossier zu schließen.
 *
 * @param int $glossar_id Glossar-Post-ID.
 * @return WP_Post[] Liste von Dossier-Posts.
 */
function hp_glossar_get_dossiers( int $glossar_id ): array {
	$dossiers = get_posts( [
		'post_type'      => 'dossier',
		'posts_per_page' => -1,
		'post_status'    => 'publish',
		'meta_query'     => [
			[
				'key'     => '_hp_dossier_begriffe',
				'value'   => (string) $glossar_id,
				'compare' => 'LIKE',
			],
		],
	] );

	// LIKE matcht auch Teilstrings (1 in 12). Sauber per
	// PHP-Filter über die exakte ID-Liste filtern.
	return array_values( array_filter( $dossiers, function ( $d ) use ( $glossar_id ) {
		$ids = hp_dossier_parse_ids( (string) get_post_meta( $d->ID, '_hp_dossier_begriffe', true ) );
		return in_array( $glossar_id, $ids, true );
	} ) );
}

/**
 * Generiert Zitations-Strings für ein Dossier in den
 * akademisch gängigen Formaten APA + BibTeX.
 *
 * Strategischer Zweck: Dossiers als Zitier-Fundamente
 * positionieren. Journalisten/Akademiker bekommen die
 * fertige Zitation in der jeweiligen Konvention zum
 * Kopieren — senkt die Hürde drastisch, das Dossier
 * tatsächlich in eigenen Texten zu verlinken.
 *
 * @param int $dossier_id Dossier-Post-ID.
 * @return array{ apa: string, bibtex: string, url: string, title: string }
 */
function hp_dossier_get_citations( int $dossier_id ): array {
	$title    = get_the_title( $dossier_id );
	$url      = get_permalink( $dossier_id );
	$author   = (string) get_post_meta( $dossier_id, '_hp_dossier_kuratiert_von', true );
	$version  = (string) get_post_meta( $dossier_id, '_hp_dossier_version', true );
	$stand    = (string) get_post_meta( $dossier_id, '_hp_dossier_stand', true );
	$site     = wp_parse_url( home_url(), PHP_URL_HOST ) ?: 'hasimuener.org';

	// Fallbacks
	if ( '' === $author ) {
		$author = 'Üner, Haşim';
	} else {
		// "Haşim Üner" → "Üner, Haşim" für APA
		$parts = preg_split( '/\s+/', trim( $author ) );
		if ( count( $parts ) >= 2 ) {
			$last  = array_pop( $parts );
			$first = implode( ' ', $parts );
			$author_apa = $last . ', ' . $first;
		} else {
			$author_apa = $author;
		}
	}
	if ( ! isset( $author_apa ) ) {
		$author_apa = $author;
	}

	// Jahr aus Stand-Datum oder Publish-Datum
	$year = $stand ? date_i18n( 'Y', strtotime( $stand ) ) : get_the_date( 'Y', $dossier_id );

	// --- APA-Style ---
	$apa_parts = [ $author_apa . ' (' . $year . ').' ];
	$apa_parts[] = $title;
	if ( $version ) {
		$apa_parts[ count( $apa_parts ) - 1 ] .= ' (Version ' . $version . ')';
	}
	$apa_parts[ count( $apa_parts ) - 1 ] .= '.';
	$apa_parts[] = $site . '.';
	$apa_parts[] = $url;
	$apa = implode( ' ', $apa_parts );

	// --- BibTeX ---
	// Cite-Key: nachname + jahr + erstes-titelwort (slugged)
	$last_only      = preg_replace( '/[^a-zA-Z]/', '', strtolower( explode( ',', $author_apa )[0] ) );
	$first_keyword  = preg_replace( '/[^a-z0-9]/', '', strtolower( strtok( $title, ' —-,:.' ) ) );
	$cite_key       = $last_only . $year . $first_keyword;
	$note_parts     = [];
	if ( $version ) { $note_parts[] = 'Version ' . $version; }
	if ( $stand )   { $note_parts[] = 'Stand: ' . date_i18n( 'j. F Y', strtotime( $stand ) ); }
	$bibtex_lines = [
		'@misc{' . $cite_key . ',',
		'  author = {' . $author . '},',
		'  title  = {' . str_replace( [ '{', '}' ], '', $title ) . '},',
		'  year   = {' . $year . '},',
		'  howpublished = {' . $site . '},',
	];
	if ( $note_parts ) {
		$bibtex_lines[] = '  note   = {' . implode( ', ', $note_parts ) . '},';
	}
	$bibtex_lines[] = '  url    = {' . $url . '}';
	$bibtex_lines[] = '}';
	$bibtex = implode( "\n", $bibtex_lines );

	return [
		'apa'    => $apa,
		'bibtex' => $bibtex,
		'url'    => $url,
		'title'  => $title,
	];
}

/**
 * Rendert die Cite-this-Box auf Dossier-Singles.
 *
 * Zwei Tabs (APA / BibTeX), Copy-Buttons pro Tab,
 * progressive Enhancement: ohne JS sind beide
 * Codeblöcke sichtbar und manuell markierbar.
 *
 * @param int $dossier_id Dossier-Post-ID.
 */
function hp_dossier_render_cite_box( int $dossier_id ): void {
	$c = hp_dossier_get_citations( $dossier_id );
	?>
	<aside class="hp-cite" aria-label="Dieses Dossier zitieren" data-cite-box>
		<header class="hp-cite__header">
			<h2 class="hp-cite__heading">Zitieren</h2>
			<p class="hp-cite__lede">Belegfertige Zitation in akademischer Konvention.</p>
		</header>

		<div class="hp-cite__tabs" role="tablist">
			<button type="button" class="hp-cite__tab is-active" role="tab" aria-selected="true" aria-controls="hp-cite-panel-apa-<?php echo (int) $dossier_id; ?>" data-cite-tab="apa">APA</button>
			<button type="button" class="hp-cite__tab" role="tab" aria-selected="false" aria-controls="hp-cite-panel-bib-<?php echo (int) $dossier_id; ?>" data-cite-tab="bibtex">BibTeX</button>
		</div>

		<div class="hp-cite__panel is-active" id="hp-cite-panel-apa-<?php echo (int) $dossier_id; ?>" role="tabpanel" data-cite-panel="apa">
			<pre class="hp-cite__text" data-cite-text><?php echo esc_html( $c['apa'] ); ?></pre>
			<button type="button" class="hp-cite__copy" data-cite-copy aria-label="APA-Zitation kopieren">
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
				<span>Kopieren</span>
			</button>
		</div>

		<div class="hp-cite__panel" id="hp-cite-panel-bib-<?php echo (int) $dossier_id; ?>" role="tabpanel" data-cite-panel="bibtex" hidden>
			<pre class="hp-cite__text" data-cite-text><?php echo esc_html( $c['bibtex'] ); ?></pre>
			<button type="button" class="hp-cite__copy" data-cite-copy aria-label="BibTeX-Zitation kopieren">
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
				<span>Kopieren</span>
			</button>
		</div>
	</aside>
	<?php
}
