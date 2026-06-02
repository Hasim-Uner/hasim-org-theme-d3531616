<?php
/**
 * Template: Wissensgraph
 *
 * Immersive, ganzseitige D3.js-Visualisierung aller Verbindungen
 * zwischen Essays, Notizen, Dossiers, Glossar-Einträgen und Themenfeldern.
 *
 * Setup: Im WP-Admin eine Seite „Wissensgraph" (Slug: wissensgraph)
 * anlegen. Das Template wird automatisch via Dateiname zugewiesen.
 *
 * @package Hasimuener_Journal
 * @since   6.0.0
 */

get_header(); ?>

<main id="main-content" class="hp-graph">

	<header class="hp-graph__intro" aria-label="Einführung in den Wissensgraph">
		<div class="hp-graph__intro-copy">
			<p class="hp-graph__eyebrow">Wissensgraph</p>
			<h1 class="hp-graph__headline">Verbindungen statt Kästen.</h1>
			<p class="hp-graph__lede">
				Essays, Notizen, Dossiers, Begriffe und Themenfelder als direkt navigierbares Wissensnetz.
			</p>
		</div>
		<div class="hp-graph__meta" aria-label="Aktueller Graph-Status">
			<p class="hp-graph__summary" id="hp-graph-summary">Graph wird lokal vorbereitet …</p>
			<div class="hp-graph__stats">
				<span class="hp-graph__stat-pill"><span class="hp-graph__stat-pill-label">Knoten</span><strong id="hp-graph-stat-nodes">0</strong></span>
				<span class="hp-graph__stat-pill"><span class="hp-graph__stat-pill-label">Verbindungen</span><strong id="hp-graph-stat-edges">0</strong></span>
				<span class="hp-graph__stat-pill"><span class="hp-graph__stat-pill-label">Typen</span><strong id="hp-graph-stat-types">5</strong></span>
			</div>
		</div>
	</header>

	<section class="hp-graph__workspace" aria-label="Arbeitsbereich des Wissensgraphen">
		<div class="hp-graph__stage">
			<div class="hp-graph__canvas-shell">
				<div class="hp-graph__toolbar" role="toolbar" aria-label="Wissensgraph-Steuerung">
					<div class="hp-graph__toolbar-group hp-graph__toolbar-group--label">
						<h2 class="hp-graph__toolbar-title">Netzwerk</h2>
					</div>
					<div class="hp-graph__toolbar-group hp-graph__search" role="search">
						<label class="screen-reader-text" for="hp-graph-search">Knoten suchen</label>
						<input id="hp-graph-search" class="hp-graph__search-input" type="search" inputmode="search" autocomplete="off" placeholder="Knoten suchen">
						<button id="hp-graph-search-clear" class="hp-graph__search-clear" type="button" aria-label="Suche leeren" hidden>&times;</button>
						<span id="hp-graph-search-count" class="hp-graph__search-count" aria-live="polite"></span>
					</div>
					<div class="hp-graph__toolbar-group hp-graph__controls" aria-label="Graph-Filter">
						<button class="hp-graph__filter hp-graph__filter--active" data-type="essay" type="button" aria-pressed="true">
							<span class="hp-graph__filter-dot hp-graph__filter-dot--essay" aria-hidden="true"></span>
							Essays
						</button>
						<button class="hp-graph__filter hp-graph__filter--active" data-type="note" type="button" aria-pressed="true">
							<span class="hp-graph__filter-dot hp-graph__filter-dot--note" aria-hidden="true"></span>
							Notizen
						</button>
						<button class="hp-graph__filter hp-graph__filter--active" data-type="dossier" type="button" aria-pressed="true">
							<span class="hp-graph__filter-dot hp-graph__filter-dot--dossier" aria-hidden="true"></span>
							Dossiers
						</button>
						<button class="hp-graph__filter hp-graph__filter--active" data-type="glossar" type="button" aria-pressed="true">
							<span class="hp-graph__filter-dot hp-graph__filter-dot--glossar" aria-hidden="true"></span>
							Glossar
						</button>
						<button class="hp-graph__filter hp-graph__filter--active" data-type="topic" type="button" aria-pressed="true">
							<span class="hp-graph__filter-dot hp-graph__filter-dot--topic" aria-hidden="true"></span>
							Themenfelder
						</button>
					</div>
					<div class="hp-graph__toolbar-group hp-graph__zoom" role="group" aria-label="Zoom-Steuerung">
						<button class="hp-graph__zoom-btn" id="hp-graph-zoom-in" type="button" aria-label="Hineinzoomen">+</button>
						<button class="hp-graph__zoom-btn" id="hp-graph-zoom-out" type="button" aria-label="Herauszoomen">−</button>
						<button class="hp-graph__zoom-btn" id="hp-graph-zoom-reset" type="button" aria-label="Zoom zurücksetzen">⟳</button>
						<button class="hp-graph__reset-btn" id="hp-graph-reset" type="button">Zurücksetzen</button>
					</div>
				</div>

				<div class="hp-graph__canvas" id="hp-graph-canvas" role="img" aria-label="Interaktiver Wissensgraph: Visualisiert Verbindungen zwischen Essays, Notizen, Dossiers, Begriffen und Themenfeldern">
					<div class="hp-graph__loading" id="hp-graph-loading">
						<p>Graph wird geladen …</p>
					</div>
					<div class="hp-graph__error" id="hp-graph-error" hidden>
						<p>Der Graph konnte nicht geladen werden. Bitte später erneut versuchen.</p>
					</div>
					<div class="hp-graph__empty" id="hp-graph-empty" hidden>
						<p>Keine Knoten im aktuellen Fokus.</p>
						<button class="hp-graph__empty-action" id="hp-graph-empty-reset" type="button">Zurücksetzen</button>
					</div>
					<div class="hp-graph__tooltip" id="hp-graph-tooltip" aria-hidden="true" hidden></div>
				</div>

				<aside class="hp-graph__detail" id="hp-graph-detail" hidden aria-live="polite">
					<div class="hp-graph__detail-inner">
						<div class="hp-graph__detail-content" id="hp-graph-detail-content">
							<!-- Wird von JS befüllt -->
						</div>
						<button class="hp-graph__detail-close" id="hp-graph-detail-close" type="button" aria-label="Details schließen">&times;</button>
					</div>
				</aside>
			</div>
		</div>

		<div class="hp-graph__footer">
			<div class="hp-graph__legend" role="img" aria-label="Legende: Farbcodes der Knotentypen">
				<div class="hp-graph__legend-item">
					<span class="hp-graph__legend-circle hp-graph__legend-circle--essay" aria-hidden="true"></span>
					<span>Essay</span>
				</div>
				<div class="hp-graph__legend-item">
					<span class="hp-graph__legend-circle hp-graph__legend-circle--note" aria-hidden="true"></span>
					<span>Notiz</span>
				</div>
				<div class="hp-graph__legend-item">
					<span class="hp-graph__legend-circle hp-graph__legend-circle--dossier" aria-hidden="true"></span>
					<span>Dossier</span>
				</div>
				<div class="hp-graph__legend-item">
					<span class="hp-graph__legend-circle hp-graph__legend-circle--glossar" aria-hidden="true"></span>
					<span>Glossar</span>
				</div>
				<div class="hp-graph__legend-item">
					<span class="hp-graph__legend-circle hp-graph__legend-circle--topic" aria-hidden="true"></span>
					<span>Themenfeld</span>
				</div>
			</div>
			<p class="hp-graph__footer-note" id="hp-graph-visible-summary">Alle Knotentypen aktiv.</p>
			<a class="hp-graph__footer-cta" href="<?php echo esc_url( home_url( '/kontakt/' ) ); ?>">Zum Thema anfragen</a>
			<p class="hp-graph__sr-summary screen-reader-text" id="hp-graph-sr-summary" aria-live="polite"></p>
		</div>
	</section>

</main>

<?php get_footer(); ?>
