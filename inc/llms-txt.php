<?php
/**
 * llms.txt — Hasimuener Journal
 *
 * Liefert eine dynamische `/llms.txt` aus kuratierten redaktionellen
 * Kern-URLs. `llms.txt` ist ein vorgeschlagener Standard (llmstxt.org),
 * der KI-Systemen einen kompakten, maschinenlesbaren Einstieg in die
 * wichtigsten Inhalte einer Site gibt — analog zu `robots.txt`, aber
 * kuratierend statt sperrend.
 *
 * Implementierung pluginlos: Die Route wird früh auf `init` abgefangen
 * (exakter Pfad-Match), Markdown ausgegeben, danach `exit`. Kein
 * Rewrite-Flush nötig — robust auch hinter statischem Nginx-Caching.
 *
 * Über das Feature-Flag `llms_txt` abschaltbar.
 *
 * @package Hasimuener_Journal
 * @since   5.8.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Fängt Requests auf `/llms.txt` ab und liefert die Markdown-Ausgabe.
 *
 * @return void
 */
function hp_llms_txt_maybe_serve(): void {
	if ( is_admin() || ! hp_feature_enabled( 'llms_txt' ) ) {
		return;
	}

	$request = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
	$path    = strtok( $request, '?' );

	if ( '/llms.txt' !== $path ) {
		return;
	}

	nocache_headers();
	header( 'Content-Type: text/plain; charset=utf-8' );

	echo hp_llms_txt_build(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- plain text, parts escaped in builder.
	exit;
}
add_action( 'init', 'hp_llms_txt_maybe_serve' );

/**
 * Baut den llms.txt-Inhalt aus Site-Metadaten und kuratierten URLs.
 *
 * @return string
 */
function hp_llms_txt_build(): string {
	$name    = wp_strip_all_tags( get_bloginfo( 'name' ) );
	$tagline = wp_strip_all_tags( get_bloginfo( 'description' ) );
	$home    = home_url( '/' );

	$lines   = [];
	$lines[] = '# ' . $name;
	$lines[] = '';
	if ( '' !== $tagline ) {
		$lines[] = '> ' . $tagline;
		$lines[] = '';
	}
	$lines[] = 'Redaktionelles Journal: Essays, Notizen, Dossiers und ein';
	$lines[] = 'kuratiertes Glossar. Diese Datei listet die wichtigsten';
	$lines[] = 'Einstiegspunkte für KI-Systeme.';
	$lines[] = '';

	// Kern-Sektionen (statisch kuratiert).
	$lines[] = '## Kernseiten';
	$lines[] = '';
	$lines[] = sprintf( '- [Startseite](%s)', esc_url_raw( $home ) );

	foreach ( hp_llms_txt_core_pages() as $slug => $label ) {
		$page = get_page_by_path( $slug );
		if ( $page instanceof WP_Post && 'publish' === $page->post_status ) {
			$lines[] = sprintf( '- [%s](%s)', $label, esc_url_raw( get_permalink( $page ) ) );
		}
	}
	$lines[] = '';

	// Archive der redaktionellen Post-Typen.
	$archives = [
		'essay'   => 'Essays',
		'note'    => 'Notizen',
		'dossier' => 'Dossiers',
		'glossar' => 'Glossar',
	];
	$archive_links = [];
	foreach ( $archives as $post_type => $label ) {
		$link = get_post_type_archive_link( $post_type );
		if ( $link ) {
			$archive_links[] = sprintf( '- [%s](%s)', $label, esc_url_raw( $link ) );
		}
	}
	if ( $archive_links ) {
		$lines[] = '## Archive';
		$lines[] = '';
		$lines   = array_merge( $lines, $archive_links );
		$lines[] = '';
	}

	// Aktuelle Essays + Dossiers als „lebende" Sektion.
	$recent = get_posts(
		[
			'post_type'        => [ 'essay', 'dossier' ],
			'post_status'      => 'publish',
			'posts_per_page'   => 15,
			'orderby'          => 'date',
			'order'            => 'DESC',
			'suppress_filters' => false,
		]
	);
	if ( $recent ) {
		$lines[] = '## Aktuelle Beiträge';
		$lines[] = '';
		foreach ( $recent as $post ) {
			$title = wp_strip_all_tags( get_the_title( $post ) );
			$lines[] = sprintf( '- [%s](%s)', $title, esc_url_raw( get_permalink( $post ) ) );
		}
		$lines[] = '';
	}

	return implode( "\n", $lines ) . "\n";
}

/**
 * Kuratierte statische Kernseiten (Slug => Label).
 *
 * @return array<string,string>
 */
function hp_llms_txt_core_pages(): array {
	return [
		'mission'      => 'Mission',
		'wissensgraph' => 'Wissensgraph',
		'kontakt'      => 'Kontakt',
	];
}
