<?php
/**
 * Mini-Graph — Wissensplattform Phase 5
 *
 * Statisches SVG mit dem aktuellen Beitrag im Zentrum und
 * 6–8 direkt verbundenen Knoten im Ring drumherum. Lese-
 * Begleiter im Sidebar von Essays, Notizen und Begriffen.
 *
 * Nutzt das existierende Datenmodell aus inc/graph-api.php
 * (Knoten + Edges, versionsbasiert gecached) und filtert
 * nur die direkten Nachbarn des focal-Posts heraus.
 *
 * Design-Prinzip (STRATEGY.md §6):
 * "Mini-Graph als Lese-Begleiter, nicht Hauptnavigation —
 *  schnell, lesbar, robust, ohne D3-Abhängigkeit."
 *
 * @package Hasimuener_Journal
 * @since   5.6.0
 */

defined( 'ABSPATH' ) || exit;

/* =========================================
   1. NACHBARN AUS GRAPH-DATEN EXTRAHIEREN
   ========================================= */

/**
 * Liefert die direkt verbundenen Knoten eines Posts,
 * sortiert nach Edge-Gewicht (DESC).
 *
 * @param int $post_id Focal-Post-ID.
 * @param int $limit   Maximale Anzahl Nachbarn (Default 8).
 * @return array<int, array<string, mixed>> Liste von Node-Objekten.
 */
function hp_minigraph_get_neighbors( int $post_id, int $limit = 8 ): array {

	$type = get_post_type( $post_id );
	if ( ! in_array( $type, [ 'essay', 'note', 'glossar' ], true ) ) {
		return [];
	}

	$focal_id = $type . '_' . $post_id;

	// Kompiliertes Graph-Payload verwenden; kein synchroner Rebuild im Render.
	if ( ! function_exists( 'hp_graph_get_compiled_data' ) ) {
		return [];
	}

	$data = hp_graph_get_compiled_data();
	if ( null === $data ) {
		if ( function_exists( 'hp_graph_schedule_rebuild' ) ) {
			hp_graph_schedule_rebuild();
		}
		return [];
	}

	if ( empty( $data['edges'] ) ) {
		return [];
	}

	if ( ! empty( $data['neighbors'][ $focal_id ] ) && is_array( $data['neighbors'][ $focal_id ] ) ) {
		$top_ids = array_slice( array_map( 'strval', $data['neighbors'][ $focal_id ] ), 0, $limit );
	} else {
		// Fallback für alte Graph-Payloads ohne Neighbor-Map.
		$neighbor_weights = [];
		foreach ( $data['edges'] as $edge ) {
			$weight = isset( $edge['weight'] ) ? (int) $edge['weight'] : 1;

			if ( $edge['source'] === $focal_id ) {
				$other = $edge['target'];
			} elseif ( $edge['target'] === $focal_id ) {
				$other = $edge['source'];
			} else {
				continue;
			}

			$neighbor_weights[ $other ] = ( $neighbor_weights[ $other ] ?? 0 ) + $weight;
		}

		if ( empty( $neighbor_weights ) ) {
			return [];
		}

		arsort( $neighbor_weights );
		$top_ids = array_slice( array_keys( $neighbor_weights ), 0, $limit );
	}

	if ( empty( $top_ids ) ) {
		return [];
	}

	// Index Knoten by ID
	$by_id = [];
	foreach ( $data['nodes'] as $node ) {
		if ( isset( $node['id'] ) ) {
			$by_id[ $node['id'] ] = $node;
		}
	}

	$neighbors = [];
	foreach ( $top_ids as $nid ) {
		if ( isset( $by_id[ $nid ] ) ) {
			$neighbors[] = $by_id[ $nid ];
		}
	}

	return $neighbors;
}

/* =========================================
   2. SVG-RENDER
   ========================================= */

/**
 * Rendert den Mini-Graph als statisches SVG.
 *
 * Layout: focal-Node im Zentrum, $limit Nachbarn auf
 * einem Ring drumherum, Linien vom Zentrum zu jedem
 * Nachbarn. Knoten-Stile nach Typ, alle Knoten klickbar.
 *
 * @param int $post_id  Focal-Post-ID.
 * @param int $limit    Maximale Anzahl Nachbarn.
 */
function hp_render_mini_graph( int $post_id, int $limit = 8 ): void {

	$neighbors = hp_minigraph_get_neighbors( $post_id, $limit );
	if ( empty( $neighbors ) ) {
		return;
	}

	$focal_label    = get_the_title( $post_id );
	$focal_initials = mb_strtoupper( mb_substr( $focal_label, 0, 2 ) );

	// SVG-Geometrie
	$cx     = 160;
	$cy     = 135;
	$radius = 92;
	$count  = count( $neighbors );

	// Knoten-Position und Style pro Typ vorberechnen
	$nodes_to_render = [];
	foreach ( $neighbors as $i => $n ) {
		$angle = ( $i / max( $count, 1 ) ) * 2 * M_PI - M_PI / 2;
		$x     = $cx + cos( $angle ) * $radius;
		$y     = $cy + sin( $angle ) * $radius;

		$type = $n['type'] ?? 'essay';
		switch ( $type ) {
			case 'glossar':
				$fill   = 'var(--wp-graph-begriff-fill)';
				$stroke = 'var(--wp-graph-begriff-stroke)';
				break;
			case 'topic':
				$fill   = 'var(--wp-graph-quelle-fill)';
				$stroke = 'var(--wp-graph-quelle-stroke)';
				break;
			case 'note':
			case 'essay':
			default:
				$fill   = 'var(--wp-graph-essay-fill)';
				$stroke = 'var(--wp-graph-essay-stroke)';
				break;
		}

		// Label-Position: oberhalb wenn obere Hälfte, sonst unterhalb
		$label_y = $y + ( $y > $cy ? 20 : -14 );

		$nodes_to_render[] = [
			'x'       => $x,
			'y'       => $y,
			'label'   => $n['label'] ?? '',
			'url'     => $n['url'] ?? '#',
			'type'    => $type,
			'fill'    => $fill,
			'stroke'  => $stroke,
			'label_y' => $label_y,
		];
	}

	?>
	<aside class="hp-mini-graph" aria-label="Im Wissensnetz verbunden">
		<h2 class="hp-mini-graph__heading">Im Netz</h2>
		<svg class="hp-mini-graph__svg" viewBox="0 0 320 270" role="img" aria-labelledby="hp-mini-graph-title-<?php echo (int) $post_id; ?>">
			<title id="hp-mini-graph-title-<?php echo (int) $post_id; ?>">
				Verbundene Knoten zu <?php echo esc_html( $focal_label ); ?>
			</title>

			<!-- Edges -->
			<?php foreach ( $nodes_to_render as $node ) : ?>
				<line x1="<?php echo (int) $cx; ?>" y1="<?php echo (int) $cy; ?>"
				      x2="<?php echo esc_attr( (string) round( $node['x'], 2 ) ); ?>"
				      y2="<?php echo esc_attr( (string) round( $node['y'], 2 ) ); ?>"
				      stroke="var(--wp-graph-edge)" stroke-width="1" />
			<?php endforeach; ?>

			<!-- Focal-Knoten -->
			<g class="hp-mini-graph__focal">
				<circle cx="<?php echo (int) $cx; ?>" cy="<?php echo (int) $cy; ?>" r="20"
				        fill="var(--wp-accent)" />
				<text x="<?php echo (int) $cx; ?>" y="<?php echo (int) ( $cy + 4 ); ?>"
				      text-anchor="middle" font-size="11" font-weight="700"
				      fill="var(--wp-ink-on-accent)" letter-spacing="0.08em"
				      font-family="var(--hj-sans)">
					<?php echo esc_html( $focal_initials ); ?>
				</text>
			</g>

			<!-- Nachbarn -->
			<?php foreach ( $nodes_to_render as $node ) :
				$href = $node['url'];
				if ( $href && '#' !== $href && false === strpos( $href, 'http' ) ) {
					$href = home_url( $href );
				}
			?>
				<a href="<?php echo esc_url( $href ); ?>" class="hp-mini-graph__node hp-mini-graph__node--<?php echo esc_attr( $node['type'] ); ?>">
					<title><?php echo esc_html( $node['label'] ); ?></title>
					<circle cx="<?php echo esc_attr( (string) round( $node['x'], 2 ) ); ?>"
					        cy="<?php echo esc_attr( (string) round( $node['y'], 2 ) ); ?>"
					        r="8"
					        fill="<?php echo esc_attr( $node['fill'] ); ?>"
					        stroke="<?php echo esc_attr( $node['stroke'] ); ?>"
					        stroke-width="1.5" />
					<text x="<?php echo esc_attr( (string) round( $node['x'], 2 ) ); ?>"
					      y="<?php echo esc_attr( (string) round( $node['label_y'], 2 ) ); ?>"
					      text-anchor="middle" font-size="10"
					      fill="var(--wp-ink-body)" font-family="var(--hj-sans)">
						<?php echo esc_html( wp_html_excerpt( $node['label'], 22, '…' ) ); ?>
					</text>
				</a>
			<?php endforeach; ?>
		</svg>

		<p class="hp-mini-graph__legend">
			<a href="<?php echo esc_url( home_url( '/wissensgraph/' ) ); ?>">Vollständiger Graph <span aria-hidden="true">&rarr;</span></a>
		</p>
	</aside>
	<?php
}
