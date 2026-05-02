<?php
/**
 * Glossar — Vernetzte Wissensbasis
 *
 * CPT „glossar" mit zwei Meta-Feldern:
 *   - Kurzdefinition (Excerpt/Zusammenfassung, 1–2 Sätze)
 *   - Kontext (editor): strukturelle Einordnung, Querverbindungen
 *
 * Auto-Linking:
 *   Glossar-Begriffe werden in Essay- und Notiz-Content automatisch
 *   beim ersten Vorkommen verlinkt (Tooltip + Link zur Glossar-Seite).
 *   Nur published-Einträge werden verlinkt; Heading-Tags werden
 *   übersprungen, um Semantik nicht zu brechen.
 *
 * @package Hasimuener_Journal
 * @since   5.1.0
 */

defined( 'ABSPATH' ) || exit;

/* =========================================
   1. CPT REGISTRIERUNG
   ========================================= */

/**
 * Registriert den Custom Post Type „glossar".
 */
function hp_register_glossar_cpt(): void {

	register_post_type( 'glossar', [
		'labels' => [
			'name'               => 'Glossar',
			'singular_name'      => 'Glossar-Eintrag',
			'add_new'            => 'Neuer Eintrag',
			'add_new_item'       => 'Neuen Glossar-Eintrag erstellen',
			'edit_item'          => 'Eintrag bearbeiten',
			'view_item'          => 'Eintrag ansehen',
			'all_items'          => 'Alle Einträge',
			'search_items'       => 'Glossar durchsuchen',
			'not_found'          => 'Keine Einträge gefunden.',
			'not_found_in_trash' => 'Keine Einträge im Papierkorb.',
		],
		'public'        => true,
		'has_archive'   => true,
		'rewrite'       => [ 'slug' => 'glossar', 'with_front' => false ],
		'menu_icon'     => 'dashicons-book-alt',
		'menu_position' => 7,
		'supports'      => [ 'title', 'editor', 'excerpt', 'revisions', 'custom-fields' ],
		'taxonomies'    => [ 'topic' ],
		'show_in_rest'  => true,
		'description'   => 'Vernetzte Wissensbasis: Begriffe, Konzepte, strukturelle Einordnungen.',
	] );
}
add_action( 'init', 'hp_register_glossar_cpt' );

/* =========================================
   2. META-FELDER
   ========================================= */

/**
 * Registriert die Glossar-spezifischen Meta-Felder.
 *
 * - _hp_glossar_kurz:     Kurzdefinition (1–2 Sätze, für Tooltip + Archiv)
 * - _hp_glossar_synonyme:  Komma-separierte Synonyme/Alternativschreibungen
 *                          für Auto-Linking (z. B. "Nordkurdistan, Bakur")
 */
function hp_register_glossar_meta(): void {

	$meta_args = [
		'object_subtype' => 'glossar',
		'show_in_rest'   => true,
		'single'         => true,
		'type'           => 'string',
		'auth_callback'  => function () {
			return current_user_can( 'edit_posts' );
		},
	];

	register_post_meta( 'glossar', '_hp_glossar_kurz', array_merge( $meta_args, [
		'sanitize_callback' => 'sanitize_textarea_field',
		'default'           => '',
	] ) );

	register_post_meta( 'glossar', '_hp_glossar_synonyme', array_merge( $meta_args, [
		'sanitize_callback' => 'sanitize_text_field',
		'default'           => '',
	] ) );
}
add_action( 'init', 'hp_register_glossar_meta' );

/* =========================================
   3. GUTENBERG SIDEBAR-PANEL
   ========================================= */

/**
 * Inline-JS Panel im Block-Editor für Glossar-Felder.
 * Kein Build-Step — läuft als Inline-Script im Editor.
 */
function hp_glossar_editor_panel(): void {

	$screen = get_current_screen();
	if ( ! $screen || 'glossar' !== $screen->post_type ) {
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

			var GlossarPanel = function() {
				var meta = useSelect( function( select ) {
					return select( 'core/editor' ).getEditedPostAttribute( 'meta' ) || {};
				} );
				var editPost = useDispatch( 'core/editor' ).editPost;

				function setMeta( key, value ) {
					var update = {};
					update[ key ] = value;
					editPost( { meta: update } );
				}

				return el( PluginPanel, {
					name:  'hp-glossar-panel',
					title: 'Glossar-Felder',
					icon:  'book-alt',
				},
					el( TextArea, {
						label: 'Kurzdefinition',
						help:  '1–2 Sätze. Wird als Tooltip und im Archiv angezeigt.',
						value: meta._hp_glossar_kurz || '',
						onChange: function( v ) { setMeta( '_hp_glossar_kurz', v ); },
						rows: 3,
					}),
					el( TextControl, {
						label: 'Synonyme / Alternativbegriffe',
						help:  'Komma-getrennt. Diese Begriffe werden ebenfalls auto-verlinkt.',
						value: meta._hp_glossar_synonyme || '',
						onChange: function( v ) { setMeta( '_hp_glossar_synonyme', v ); },
					})
				);
			};

			wp.plugins.registerPlugin( 'hp-glossar-panel', {
				render: GlossarPanel,
				icon:   'book-alt',
			} );
		} )();
	" );
}
add_action( 'enqueue_block_editor_assets', 'hp_glossar_editor_panel' );

/* =========================================
   4. AUTO-LINKING IN CONTENT
   ========================================= */

/**
 * Ersetzt beim ersten Vorkommen jedes Glossar-Begriffs im Content
 * einen verlinkten Tooltip.
 *
 * Regeln:
 * - Nur in essay/note/page Singular-Ansichten aktiv
 * - Nur published Glossar-Einträge
 * - Überspringt <h1>–<h6>, <a>, <code>, <pre>, <script>, <style>, <span class="hp-glossar-term">
 * - Maximal 1 Verlinkung pro Begriff pro Beitrag
 * - Ergebnis wird versioniert gecacht (auto-invalidiert bei Glossar-Änderungen)
 *
 * @param string $content Post-Content.
 * @return string
 */
function hp_glossar_auto_link( string $content ): string {

	// Nur in Essay/Note/Page Singular-Ansichten
	if ( ! is_singular( [ 'essay', 'note', 'page' ] ) ) {
		return $content;
	}

	// Nicht auf Glossar-Einträgen selbst (verhindert Selbstverlinkung)
	if ( 'glossar' === get_post_type() ) {
		return $content;
	}

	$post_id       = get_the_ID();
	$glossar_ver   = (int) get_option( 'hp_glossar_version', 0 );
	$cache_key     = 'hp_gl_' . $post_id . '_v' . $glossar_ver;

	// Transient-Cache prüfen
	$cached = get_transient( $cache_key );
	if ( false !== $cached ) {
		return $cached;
	}

	// Glossar-Einträge laden
	$entries = get_posts( [
		'post_type'      => 'glossar',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'fields'         => 'ids',
	] );

	if ( empty( $entries ) ) {
		return $content;
	}

	// Begriffe sammeln: Titel + Synonyme → URL + Kurzdefinition
	$terms = [];
	foreach ( $entries as $entry_id ) {
		$title   = get_the_title( $entry_id );
		$url     = get_permalink( $entry_id );
		$kurz    = get_post_meta( $entry_id, '_hp_glossar_kurz', true );
		$tooltip = esc_attr( wp_strip_all_tags( $kurz ) );

		// Hauptbegriff
		if ( $title ) {
			$terms[] = [
				'pattern' => preg_quote( $title, '/' ),
				'url'     => $url,
				'tooltip' => $tooltip,
				'label'   => $title,
			];
		}

		// Synonyme
		$synonyme = get_post_meta( $entry_id, '_hp_glossar_synonyme', true );
		if ( $synonyme ) {
			foreach ( explode( ',', $synonyme ) as $syn ) {
				$syn = trim( $syn );
				if ( $syn ) {
					$terms[] = [
						'pattern' => preg_quote( $syn, '/' ),
						'url'     => $url,
						'tooltip' => $tooltip,
						'label'   => $syn,
					];
				}
			}
		}
	}

	if ( empty( $terms ) ) {
		return $content;
	}

	// Längere Begriffe zuerst (verhindert Teilersetzung)
	usort( $terms, function ( $a, $b ) {
		return mb_strlen( $b['pattern'] ) - mb_strlen( $a['pattern'] );
	} );

	// Gutenberg-Kommentare entfernen → nach Verarbeitung zurücksetzen
	// (verhindert, dass Kommentare das Tag-Parsing stören)
	$placeholders = [];
	$content = preg_replace_callback(
		'/<!--.*?-->/s',
		function ( $match ) use ( &$placeholders ) {
			$key = '%%HP_CMT_' . count( $placeholders ) . '%%';
			$placeholders[ $key ] = $match[0];
			return $key;
		},
		$content
	);

	/*
	 * Tags aufteilen: HTML-Tags vs. Text-Nodes.
	 * Überspringe: Headings, Links, Code, Pre, Script, Style, bereits verlinkte Glossar-Spans.
	 *
	 * WICHTIG: \b nach dem Tag-Namen statt [\s>] — damit auch
	 * einfache Tags wie <h2>, </a>, <code> korrekt gematcht werden.
	 */
	$skip_tags = 'h[1-6]|a|code|pre|script|style|span';
	$split_re  = '/(<\/?(?:' . $skip_tags . ')\b[^>]*>)/i';
	$parts     = preg_split( $split_re, $content, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY );

	$in_skip   = 0;
	$linked    = []; // Bereits verlinkte Begriffe (max 1×)
	$processed = '';

	foreach ( $parts as $part ) {

		// Öffnender Skip-Tag?
		if ( preg_match( '/^<((?:' . $skip_tags . ')\b)/i', $part, $m ) && $part[1] !== '/' ) {
			$in_skip++;
			$processed .= $part;
			continue;
		}

		// Schließender Skip-Tag?
		if ( preg_match( '/^<\/((?:' . $skip_tags . ')\b)/i', $part ) ) {
			$in_skip = max( 0, $in_skip - 1 );
			$processed .= $part;
			continue;
		}

		// Innerhalb eines Skip-Tags → unverändert
		if ( $in_skip > 0 ) {
			$processed .= $part;
			continue;
		}

		// Ist es ein anderer HTML-Tag? → unverändert
		if ( isset( $part[0] ) && $part[0] === '<' && preg_match( '/^<[^>]+>$/', $part ) ) {
			$processed .= $part;
			continue;
		}

		// Text-Node: Begriffe ersetzen (nur erstes Vorkommen pro Begriff)
		foreach ( $terms as $term ) {
			if ( isset( $linked[ $term['pattern'] ] ) ) {
				continue;
			}

			$regex = '/\b(' . $term['pattern'] . ')\b/u';

			if ( preg_match( $regex, $part ) ) {
				$replacement = sprintf(
					'<span class="hp-glossar-term hp-begriff-chip" data-term="%s" data-def="%s" data-url="%s" tabindex="0" role="button" aria-describedby="hp-gtt">$1</span>',
					esc_attr( $term['label'] ),
					esc_attr( $term['tooltip'] ),
					esc_url( $term['url'] )
				);

				$part = preg_replace( $regex, $replacement, $part, 1 );
				$linked[ $term['pattern'] ] = true;
			}
		}

		$processed .= $part;
	}

	// Gutenberg-Kommentare wiederherstellen
	if ( $placeholders ) {
		$processed = str_replace( array_keys( $placeholders ), array_values( $placeholders ), $processed );
	}

	// Cache für 24 Stunden (auto-invalidiert durch Glossar-Version)
	set_transient( $cache_key, $processed, DAY_IN_SECONDS );

	return $processed;
}
add_filter( 'the_content', 'hp_glossar_auto_link', 20 );

/* =========================================
   5. CACHE INVALIDIERUNG (versionbasiert)
   ========================================= */

/**
 * Bumpt die Glossar-Version und löscht Transient-Caches
 * wenn ein Glossar-Eintrag erstellt, aktualisiert oder gelöscht wird.
 *
 * Durch die Version im Cache-Key sind alte Transients automatisch
 * stale — neue Requests erzeugen frische Caches.
 */
function hp_glossar_flush_cache( int $post_id ): void {

	// Revisions und Autosaves ignorieren
	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}

	if ( 'glossar' !== get_post_type( $post_id ) ) {
		return;
	}

	// Version bumpen → alte Cache-Keys werden irrelevant
	$new_version = (int) get_option( 'hp_glossar_version', 0 ) + 1;
	update_option( 'hp_glossar_version', $new_version, false );

	// Alte Transients aufräumen (SQL für DB-backed Transients)
	global $wpdb;
	$wpdb->query(
		"DELETE FROM {$wpdb->options}
		 WHERE option_name LIKE '_transient_hp_gl_%'
		    OR option_name LIKE '_transient_timeout_hp_gl_%'"
	);
}
add_action( 'save_post_glossar', 'hp_glossar_flush_cache' );
add_action( 'delete_post',       'hp_glossar_flush_cache' );

/**
 * Spezialfall: Glossar-Eintrag wechselt Status (draft → publish).
 * save_post feuert hier auch, aber transition_post_status
 * stellt sicher, dass der Cache IMMER invalidiert wird.
 */
function hp_glossar_flush_on_status_change( string $new_status, string $old_status, \WP_Post $post ): void {
	if ( 'glossar' !== $post->post_type ) {
		return;
	}
	if ( $new_status !== $old_status ) {
		hp_glossar_flush_cache( $post->ID );
	}
}
add_action( 'transition_post_status', 'hp_glossar_flush_on_status_change', 10, 3 );

/* =========================================
   6. EINMALIGE CACHE-MIGRATION (Markup-Wechsel)
   ========================================= */

/**
 * Bumpt die Glossar-Version einmal nach Theme-Update,
 * damit Auto-Link-Caches mit altem Markup (.hp-glossar-term ohne
 * .hp-begriff-chip) beim nächsten Render verworfen werden.
 *
 * Das Flag verhindert, dass die Migration mehrfach läuft.
 * Bei künftigen Markup-Änderungen den Flag-Namen erhöhen
 * (chip_v2, chip_v3, …).
 */
function hp_glossar_chip_markup_migration(): void {
	if ( get_option( 'hp_glossar_chip_v1' ) ) {
		return;
	}

	$new_version = (int) get_option( 'hp_glossar_version', 0 ) + 1;
	update_option( 'hp_glossar_version', $new_version, false );

	global $wpdb;
	$wpdb->query(
		"DELETE FROM {$wpdb->options}
		 WHERE option_name LIKE '_transient_hp_gl_%'
		    OR option_name LIKE '_transient_timeout_hp_gl_%'"
	);

	update_option( 'hp_glossar_chip_v1', true, false );
}
add_action( 'init', 'hp_glossar_chip_markup_migration', 25 );
