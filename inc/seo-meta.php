<?php
/**
 * SEO-Meta — Hasimuener Journal
 *
 * Leichtgewichtiger Ersatz für The SEO Framework.
 * Liefert Meta-Description, Open Graph und Twitter Cards
 * direkt aus dem Theme — kein Plugin nötig.
 *
 * Architektur:
 * - register_post_meta → Gutenberg-Zugriff + REST API
 * - Inline-JS Sidebar-Panel → Beschreibungsfeld im Editor
 * - wp_head-Output → description, OG, Twitter Card
 *
 * Fallback-Kette für Description:
 * 1. Manuelles Meta-Feld `_hp_meta_description`
 * 2. Beitrags-Excerpt (falls vorhanden)
 * 3. Automatischer Trim auf 160 Zeichen aus Post-Content
 * 4. Site-Tagline (Startseite / Archive)
 *
 * @package Hasimuener_Journal
 * @since   4.1.0
 */

defined( 'ABSPATH' ) || exit;

/* =========================================
   1. META-FELD REGISTRIEREN
   ========================================= */

/**
 * Registriert eine dedizierte Social-Share-Bildgröße (1200×675, 16:9).
 *
 * Garantiert ein gut komponiertes OG-/Twitter-Card-Bild,
 * auch wenn das Beitragsbild größer/kleiner ist als das
 * von Plattformen erwartete 16:9-Format.
 */
function hp_register_og_image_size(): void {
	add_image_size( 'hp-og', 1200, 675, true );
}
add_action( 'after_setup_theme', 'hp_register_og_image_size' );

/**
 * Registriert `_hp_meta_description` für Essays und Notes.
 *
 * Das Feld ist REST-fähig und im Block-Editor verfügbar.
 * auth_callback beschränkt Zugriff auf Nutzer mit edit_posts.
 */
function hp_register_seo_meta(): void {
	$args = [
		'type'              => 'string',
		'single'            => true,
		'sanitize_callback' => 'sanitize_text_field',
		'auth_callback'     => static function(): bool {
			return current_user_can( 'edit_posts' );
		},
		'show_in_rest'      => true,
		'default'           => '',
	];

	register_post_meta( 'essay', '_hp_meta_description', $args );
	register_post_meta( 'note', '_hp_meta_description', $args );
	register_post_meta( 'post', '_hp_meta_description', $args );
	register_post_meta( 'page', '_hp_meta_description', $args );
}
add_action( 'init', 'hp_register_seo_meta' );

/* =========================================
   2. GUTENBERG SIDEBAR PANEL
   ========================================= */

/**
 * Editor-Panel: Meta-Description für SEO.
 *
 * Inline-JS-Ansatz identisch zu meta-fields.php —
 * kein Build-Step, kein React-Overhead.
 * Wird auf allen unterstützten Post-Types geladen.
 */
function hp_seo_meta_editor_assets(): void {
	$screen = get_current_screen();
	if ( ! $screen ) {
		return;
	}

	$supported = [ 'essay', 'note', 'post', 'page' ];
	if ( ! in_array( $screen->post_type, $supported, true ) ) {
		return;
	}

	wp_enqueue_script(
		'hp-seo-meta-panel',
		false, // Inline-Script
		[ 'wp-plugins', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-data', 'wp-compose' ],
	);

	$inline_js = <<<'JS'
( function() {
    var el          = wp.element.createElement;
    var PluginPanel = wp.editPost.PluginDocumentSettingPanel;
    var TextControl = wp.components.TextareaControl;
    var useSelect   = wp.data.useSelect;
    var useDispatch = wp.data.useDispatch;

    var META_KEY  = '_hp_meta_description';
    var MAX_CHARS = 160;

    function SeoMetaPanel() {
        var meta = useSelect( function( select ) {
            return select( 'core/editor' ).getEditedPostAttribute( 'meta' ) || {};
        }, [] );

        var editPost = useDispatch( 'core/editor' ).editPost;
        var value    = meta[ META_KEY ] || '';
        var charInfo = value.length + ' / ' + MAX_CHARS + ' Zeichen';

        return el( PluginPanel, {
            name:  'hp-seo-meta',
            title: 'SEO — Meta-Beschreibung',
            icon:  'search',
        },
            el( TextControl, {
                label:    'Meta-Description',
                help:     charInfo + ' — Wird in Google-Snippets und Social-Media-Previews angezeigt.',
                value:    value,
                onChange: function( newVal ) {
                    if ( newVal.length <= MAX_CHARS ) {
                        var newMeta = {};
                        newMeta[ META_KEY ] = newVal;
                        editPost( { meta: newMeta } );
                    }
                },
                rows: 3,
            })
        );
    }

    wp.plugins.registerPlugin( 'hp-seo-meta', {
        render: SeoMetaPanel,
        icon:   'search',
    });
})();
JS;

	wp_add_inline_script( 'hp-seo-meta-panel', $inline_js );
}
add_action( 'enqueue_block_editor_assets', 'hp_seo_meta_editor_assets' );

/* =========================================
   3. MISSION: TITLE + DESCRIPTION OVERRIDES
   ========================================= */

/**
 * Prüft, ob die aktuelle Anfrage die Mission-Seite ist.
 *
 * @return bool
 */
function hp_is_mission_page(): bool {
	return ! is_admin() && is_page( 'mission' );
}

/**
 * Erzwingt einen stabilen Dokumenttitel für /mission/.
 *
 * @param string $title Vorheriger Titel.
 * @return string
 */
function hp_filter_mission_document_title( string $title ): string {
	if ( ! hp_is_mission_page() ) {
		return $title;
	}

	return 'Mission – Haşim Üner';
}
add_filter( 'pre_get_document_title', 'hp_filter_mission_document_title' );

/**
 * Stabiler Dokumenttitel für die Startseite.
 *
 * @param string $title Vorheriger Titel.
 * @return string
 */
function hp_filter_front_page_document_title( string $title ): string {
	if ( is_admin() || ( ! is_front_page() && ! is_home() ) ) {
		return $title;
	}

	return 'Macht. Medien. Perspektive. – Haşim Üner';
}
add_filter( 'pre_get_document_title', 'hp_filter_front_page_document_title' );

/**
 * Vereinheitlicht die Title-Tags für Archive, Suche und 404.
 *
 * Standard-WP-Titel wirken auf Archiven oft generisch
 * („Essays – Site-Name"). Wir setzen präzise, gleich
 * formatierte Titel mit Kontext-Suffix, damit die SERP-
 * Snippets klarer sind.
 *
 * @param array<string,string> $parts Title-Bestandteile.
 * @return array<string,string>
 */
function hp_filter_document_title_parts( array $parts ): array {
	if ( is_post_type_archive( 'essay' ) ) {
		$parts['title'] = 'Essays — Langform-Analysen';
	} elseif ( is_post_type_archive( 'note' ) ) {
		$parts['title'] = 'Notizen — Beobachtungen & Fragmente';
	} elseif ( is_post_type_archive( 'glossar' ) ) {
		$parts['title'] = 'Glossar — Begriffsdefinitionen';
	} elseif ( is_post_type_archive( 'dossier' ) ) {
		$parts['title'] = 'Dossiers — Kuratierte Wissensknoten';
	} elseif ( is_tax( 'topic' ) ) {
		$parts['title'] = 'Thema: ' . single_term_title( '', false );
	} elseif ( is_search() ) {
		$parts['title'] = sprintf( 'Suche: %s', get_search_query() );
	} elseif ( is_404() ) {
		$parts['title'] = 'Seite nicht gefunden';
	}

	return $parts;
}
add_filter( 'document_title_parts', 'hp_filter_document_title_parts' );

/* =========================================
   4. DESCRIPTION RESOLVER
   ========================================= */

/**
 * Ermittelt die beste verfügbare Description.
 *
 * Fallback-Kette: Meta-Feld → Excerpt → Content-Trim → Tagline.
 * Maximal 160 Zeichen, kein HTML.
 *
 * @return string Bereinigte Description oder leer.
 */
function hp_get_meta_description(): string {
	$desc = '';

	if ( hp_is_mission_page() ) {
		return 'Essays und Notizen über Macht, Medien, Erinnerung, Sprache und Gesellschaft – mit dem Versuch, Verständigung zwischen Perspektiven offenzuhalten.';
	}

	if ( is_front_page() || is_home() ) {
		$desc = 'Essays und Notizen über Macht, Medien, Erinnerung, Sprache und Gesellschaft. Von Haşim Üner.';
	} elseif ( is_singular() ) {
		$post = get_queried_object();
		if ( ! ( $post instanceof WP_Post ) ) {
			return '';
		}

		// 1. Manuelles Feld
		$custom = get_post_meta( $post->ID, '_hp_meta_description', true );
		if ( $custom ) {
			$desc = $custom;
		}
		// 2. Beitrags-Excerpt
		elseif ( has_excerpt( $post->ID ) ) {
			$desc = wp_strip_all_tags( get_the_excerpt( $post ) );
		}
		// 3. Content-Trim
		else {
			$desc = wp_trim_words( wp_strip_all_tags( $post->post_content ), 25, ' …' );
		}
	} elseif ( is_post_type_archive() ) {
		$obj = get_queried_object();
		if ( $obj && ! empty( $obj->description ) ) {
			$desc = $obj->description;
		} else {
			$cpt_descriptions = [
				'essay'   => 'Langform-Essays zu Macht, Medien, Erinnerung, Sprache und Gesellschaft — analytisch, mit Apparat und Quellen.',
				'note'    => 'Kurze Notizen, Fragmente und Beobachtungen — schneller getaktet als Essays, mit Quellenverweisen.',
				'glossar' => 'Glossar: Begriffsdefinitionen aus Medienwissenschaft, Diskursanalyse und Gesellschaftstheorie.',
				'dossier' => 'Dossiers: kuratierte Wissensknoten mit Leseplan, Begriffsapparat und Quellen zu einem Themenfeld.',
			];

			if ( $obj && isset( $cpt_descriptions[ $obj->name ] ) ) {
				$desc = $cpt_descriptions[ $obj->name ];
			}
		}
	} elseif ( is_tax() || is_category() || is_tag() ) {
		$desc = term_description();
		$desc = wp_strip_all_tags( $desc );
	}

	if ( ! $desc ) {
		$desc = 'Essays und Notizen über Macht, Medien, Erinnerung, Sprache und Gesellschaft.';
	}

	// Auf 160 Zeichen begrenzen
	if ( mb_strlen( $desc ) > 160 ) {
		$desc = mb_substr( $desc, 0, 157 ) . '…';
	}

	return trim( $desc );
}

/* =========================================
   5. HEAD-OUTPUT: META + OPEN GRAPH + TWITTER
   ========================================= */

/**
 * Gibt Meta-Description, Open-Graph- und Twitter-Card-Tags aus.
 *
 * Priorität 5 → vor Theme-/Plugin-Ausgaben.
 * Prüft, ob The SEO Framework aktiv ist — falls ja,
 * wird NICHTS ausgegeben (Dopplung vermeiden).
 */
function hp_output_seo_meta_tags(): void {
	// Sicherheitsnetz: Falls TSF doch noch aktiv ist, nichts ausgeben
	if ( defined( 'THE_SEO_FRAMEWORK_VERSION' ) ) {
		return;
	}

	$desc      = hp_get_meta_description();
	$title     = wp_get_document_title();
	$url       = hp_get_current_url();
	$site_name = get_bloginfo( 'name' );
	$locale    = get_locale();
	$image_data = hp_get_seo_image_data();
	$image      = $image_data['url'] ?? null;

	echo "\n<!-- Haşim Üner: SEO-Meta -->\n";

	// Canonical URL
	printf( '<link rel="canonical" href="%s" />' . "\n", esc_url( $url ) );

	// Meta-Description
	if ( $desc ) {
		printf( '<meta name="description" content="%s" />' . "\n", esc_attr( $desc ) );
	}

	// Open Graph
	printf( '<meta property="og:title" content="%s" />' . "\n", esc_attr( $title ) );
	printf( '<meta property="og:url" content="%s" />' . "\n", esc_url( $url ) );
	printf( '<meta property="og:site_name" content="%s" />' . "\n", esc_attr( $site_name ) );
	printf( '<meta property="og:locale" content="%s" />' . "\n", esc_attr( $locale ) );

	if ( $desc ) {
		printf( '<meta property="og:description" content="%s" />' . "\n", esc_attr( $desc ) );
	}

	if ( hp_is_social_article_context() ) {
		echo '<meta property="og:type" content="article" />' . "\n";
		printf(
			'<meta property="article:published_time" content="%s" />' . "\n",
			esc_attr( get_the_date( 'c' ) )
		);
		printf(
			'<meta property="article:modified_time" content="%s" />' . "\n",
			esc_attr( get_the_modified_date( 'c' ) )
		);
	} else {
		echo '<meta property="og:type" content="website" />' . "\n";
	}

	if ( $image ) {
		printf( '<meta property="og:image" content="%s" />' . "\n", esc_url( $image ) );
		printf( '<meta property="og:image:secure_url" content="%s" />' . "\n", esc_url( $image ) );

		if ( ! empty( $image_data['type'] ) ) {
			printf( '<meta property="og:image:type" content="%s" />' . "\n", esc_attr( $image_data['type'] ) );
		}

		if ( ! empty( $image_data['width'] ) ) {
			printf( '<meta property="og:image:width" content="%d" />' . "\n", (int) $image_data['width'] );
		}

		if ( ! empty( $image_data['height'] ) ) {
			printf( '<meta property="og:image:height" content="%d" />' . "\n", (int) $image_data['height'] );
		}

		printf(
			'<meta property="og:image:alt" content="%s" />' . "\n",
			esc_attr( $image_data['alt'] ?? $title )
		);
	}

	if ( hp_is_social_article_context() ) {
		echo '<meta property="article:author" content="Haşim Üner" />' . "\n";
	}

	// Twitter Card
	echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
	echo '<meta name="twitter:site" content="@_0239983326111" />' . "\n";
	echo '<meta name="twitter:creator" content="@_0239983326111" />' . "\n";
	printf( '<meta name="twitter:title" content="%s" />' . "\n", esc_attr( $title ) );

	if ( $desc ) {
		printf( '<meta name="twitter:description" content="%s" />' . "\n", esc_attr( $desc ) );
	}

	if ( $image ) {
		printf( '<meta name="twitter:image" content="%s" />' . "\n", esc_url( $image ) );

		if ( ! empty( $image_data['alt'] ) ) {
			printf( '<meta name="twitter:image:alt" content="%s" />' . "\n", esc_attr( $image_data['alt'] ) );
		}
	}
}
add_action( 'wp_head', 'hp_output_seo_meta_tags', 3 );

/* =========================================
   5. HILFSFUNKTIONEN
   ========================================= */

/**
 * Bestimmt, ob Social-Meta als Artikel und nicht als Website laufen soll.
 *
 * Pages wie Startseite, Mission oder Impressum sollen nicht als Article
 * ausgezeichnet werden. Essays, Notes und klassische Posts schon.
 *
 * @return bool
 */
function hp_is_social_article_context(): bool {
	return is_singular( [ 'essay', 'note', 'post' ] );
}

/**
 * Liefert die Post-ID des Hero-Essays auf der Startseite.
 *
 * @return int
 */
function hp_get_front_page_hero_post_id(): int {
	static $post_id = null;

	if ( null !== $post_id ) {
		return $post_id;
	}

	$post_ids = get_posts(
		[
			'post_type'           => 'essay',
			'posts_per_page'      => 1,
			'post_status'         => 'publish',
			'fields'              => 'ids',
			'orderby'             => 'date',
			'order'               => 'DESC',
			'no_found_rows'       => true,
			'ignore_sticky_posts' => true,
		]
	);

	$post_id = ! empty( $post_ids[0] ) ? (int) $post_ids[0] : 0;

	return $post_id;
}

/**
 * Liefert vollständige Social-Metadaten für ein Attachment-Bild.
 *
 * @param int          $attachment_id Attachment-ID.
 * @param string|int[] $size          Bildgröße.
 * @return array<string, int|string>|null
 */
function hp_get_attachment_social_image_data( int $attachment_id, $size = 'hp-og' ): ?array {
	if ( $attachment_id <= 0 ) {
		return null;
	}

	$image = wp_get_attachment_image_src( $attachment_id, $size );

	// Fallback auf `full`, falls die `hp-og`-Größe (noch) nicht generiert ist —
	// z. B. bei älteren Bildern vor Theme-Update.
	if ( ( ! $image || empty( $image[0] ) ) && 'hp-og' === $size ) {
		$image = wp_get_attachment_image_src( $attachment_id, 'full' );
	}

	if ( ! $image || empty( $image[0] ) ) {
		return null;
	}

	$alt = trim( (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) );
	if ( ! $alt ) {
		$alt = get_the_title( $attachment_id );
	}

	return [
		'url'    => $image[0],
		'width'  => ! empty( $image[1] ) ? (int) $image[1] : 0,
		'height' => ! empty( $image[2] ) ? (int) $image[2] : 0,
		'alt'    => $alt,
		'type'   => (string) get_post_mime_type( $attachment_id ),
	];
}

/**
 * Aktuelle URL sauber ermitteln.
 *
 * @return string Vollständige URL der aktuellen Seite.
 */
function hp_get_current_url(): string {
	if ( is_singular() ) {
		return get_permalink();
	}

	if ( is_front_page() || is_home() ) {
		return home_url( '/' );
	}

	if ( is_post_type_archive() ) {
		return get_post_type_archive_link( get_queried_object()->name );
	}

	if ( is_tax() || is_category() || is_tag() ) {
		return get_term_link( get_queried_object() );
	}

	// Fallback
	return home_url( add_query_arg( null, null ) );
}

/**
 * Bestes verfügbares Bild für Social-Previews inklusive Metadaten.
 *
 * Reihenfolge:
 * 1. Hero-Essay der Startseite
 * 2. Beitragsbild des aktuellen Inhalts
 * 3. Erstes Bild im Content
 * 4. Site-Icon
 * 5. Theme-Fallback
 *
 * @return array<string, int|string>|null
 */
function hp_get_seo_image_data(): ?array {
	if ( is_front_page() || is_home() ) {
		$hero_post_id = hp_get_front_page_hero_post_id();

		if ( $hero_post_id && has_post_thumbnail( $hero_post_id ) ) {
			$image_data = hp_get_attachment_social_image_data( (int) get_post_thumbnail_id( $hero_post_id ) );
			if ( $image_data ) {
				return $image_data;
			}
		}
	}

	if ( is_singular() ) {
		$post = get_queried_object();
		if ( ! ( $post instanceof WP_Post ) ) {
			return null;
		}

		// Beitragsbild
		if ( has_post_thumbnail( $post->ID ) ) {
			$image_data = hp_get_attachment_social_image_data( (int) get_post_thumbnail_id( $post->ID ) );
			if ( $image_data ) {
				return $image_data;
			}
		}

		// Erstes Bild im Content als Fallback
		preg_match( '/<img[^>]+src=["\']([^"\']+)/i', $post->post_content, $matches );
		if ( ! empty( $matches[1] ) ) {
			$ext  = strtolower( pathinfo( wp_parse_url( $matches[1], PHP_URL_PATH ) ?? '', PATHINFO_EXTENSION ) );
			$mime = [
				'jpg'  => 'image/jpeg',
				'jpeg' => 'image/jpeg',
				'png'  => 'image/png',
				'webp' => 'image/webp',
				'gif'  => 'image/gif',
				'avif' => 'image/avif',
			][ $ext ] ?? '';

			$fallback = [
				'url' => $matches[1],
				'alt' => get_the_title( $post ),
			];

			if ( $mime ) {
				$fallback['type'] = $mime;
			}

			return $fallback;
		}
	}

	// Site-Icon als letzter Fallback
	$site_icon_id = (int) get_option( 'site_icon' );
	if ( $site_icon_id ) {
		$image_data = hp_get_attachment_social_image_data( $site_icon_id );
		if ( $image_data ) {
			return $image_data;
		}
	}

	$site_icon = get_site_icon_url( 512 );
	if ( $site_icon ) {
		return [
			'url' => $site_icon,
			'alt' => get_bloginfo( 'name' ),
		];
	}

	$fallback_path = get_stylesheet_directory() . '/assets/images/diaspora-rose-realistic.jpg';
	if ( file_exists( $fallback_path ) ) {
		$fallback_size = wp_getimagesize( $fallback_path );

		return [
			'url'    => get_stylesheet_directory_uri() . '/assets/images/diaspora-rose-realistic.jpg',
			'width'  => ! empty( $fallback_size[0] ) ? (int) $fallback_size[0] : 0,
			'height' => ! empty( $fallback_size[1] ) ? (int) $fallback_size[1] : 0,
			'alt'    => get_bloginfo( 'name' ),
			'type'   => 'image/jpeg',
		];
	}

	return null;
}

/**
 * Nur die Bild-URL für Rückwärtskompatibilität.
 *
 * @return string|null
 */
function hp_get_seo_image(): ?string {
	$image_data = hp_get_seo_image_data();

	return $image_data['url'] ?? null;
}
