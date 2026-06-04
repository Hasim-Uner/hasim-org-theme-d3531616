# Agent Ownership Model

Stand: 2026-06-02

Diese Datei ist ein Arbeitsvertrag fuer Cursor, Claude Code, OpenAI Agents und menschliche Reviewer. Agenten sollen vor Aenderungen zusaetzlich `docs/change-impact.yml` lesen.

## Globale Regeln

- `inc/contact-local.php` nie lesen, zitieren, editieren oder committen. Nur `inc/contact-local.php.example` ist dokumentierbar.
- Keine SEO-, Graph- oder Schema-Aenderung ohne Blick auf `inc/seo-schema.php`, `inc/graph-api.php`, `inc/glossary.php`, `inc/dossier.php` und `docs/change-impact.yml`.
- Keine neuen globalen Assets ohne Eintrag in `docs/architecture.yml` und `docs/ASSET_MATRIX.md`.
- Keine Feature-CSS-Erweiterung in `style.css`, wenn eine bedingte Datei logisch passt.
- Keine Aenderung an public `hp_*` APIs ohne Rueckwaertskompatibilitaet oder dokumentierte Migration.
- Keine Aenderung an `nexus_*` SEO-Cockpit-Funktionen ohne Admin-/Capability-/Cache-Kontext.

## SEO Agent

Zustaendigkeiten:

- Meta-Descriptions, Canonicals, Open Graph, Twitter Cards.
- JSON-LD, Breadcrumbs, Sitemap, Robots, hreflang, rel=me.
- Search Console und SEO-Cockpit-Insights.
- Entity-ID-Policy und Schema.org-Mapping.

Dateien:

- `inc/seo-meta.php`
- `inc/seo-schema.php`
- `inc/seo/schema-*.php`
- `inc/seo-hygiene.php`
- `inc/sitemap.php`
- `inc/breadcrumbs.php`
- `inc/llms-txt.php`
- `inc/seo-cockpit/*`
- `robots.txt`
- `docs/change-impact.yml`

Verbote:

- Kein Template-Markup umbauen, wenn nur Schema betroffen ist.
- Keine Indexierungslogik aendern ohne Sitemap-/Robots-Folge zu pruefen.
- Keine Secrets aus SEO-Cockpit-Konstanten oder Optionen dokumentieren.

Abhaengigkeiten:

- Knowledge Graph Agent fuer Dossier/Glossar/Topic-Entitaeten.
- Content Agent fuer redaktionelle Meta-Felder.
- Analytics Agent fuer SEO-Cockpit-Auswertung.

## Knowledge Graph Agent

Zustaendigkeiten:

- Graph-Datenmodell, Nodes, Edges, Rebuild, Cache.
- Glossar-Beziehungen, Dossier-Relationen, Topic-Verknuepfungen.
- Mini-Graph und Wissensgraph-REST-Payload.
- Schema-kompatible Entity-Relationen.

Dateien:

- `inc/graph-api.php`
- `inc/mini-graph.php`
- `inc/glossary.php`
- `inc/dossier.php`
- `inc/taxonomies.php`
- `page-wissensgraph.php`
- `assets/js/graph.js`
- `assets/css/pages/wissensgraph.css`

Verbote:

- Kein synchroner Graph-Rebuild im Render-Pfad.
- Keine neue Relation nur im Template verstecken, wenn sie in den Graph gehoert.
- Keine Dossier-/Glossar-ID-Listen ohne exakte Parse-/Validation-Logik verwenden.
- Keine eigenen Glossar-Titel-/Synonym-Scans bauen; fuer Term-Matches `hp_glossar_get_term_index()` nutzen.

Abhaengigkeiten:

- SEO Agent fuer JSON-LD-Entsprechung.
- Performance Agent fuer Rebuild- und Cache-Kosten.
- Frontend Agent fuer Graph-UI.

## Content Agent

Zustaendigkeiten:

- CPTs, Taxonomien, Glossar-/Dossier-Meta, Templates fuer Essays, Notes, Glossar, Dossiers und Topics.
- Redaktionelle Beziehungspflege: Leseplan, Begriffsapparat, zentrale Begriffe, verwandte Inhalte.
- Kommentare, Newsletter-Einbettungen und redaktionelle UX im Content-Kontext.

Dateien:

- `inc/post-types.php`
- `inc/taxonomies.php`
- `inc/meta-fields.php`
- `inc/glossary.php`
- `inc/dossier.php`
- `single-essay.php`
- `single-note.php`
- `single-glossar.php`
- `single-dossier.php`
- `taxonomy-topic.php`
- `archive-*.php`
- `template-parts/*`

Verbote:

- Keine Slug-/Rewrite-Aenderungen ohne Redirect-/Sitemap-Konzept.
- Keine neuen Meta-Felder ohne REST-/sanitize-/auth_callback.
- Keine Seeder-Aenderung ohne Version-Flag.

Abhaengigkeiten:

- SEO Agent fuer Schema und Snippets.
- Knowledge Graph Agent fuer Beziehungsmodell.
- Frontend Agent fuer Markup/CSS.

## Frontend Agent

Zustaendigkeiten:

- CSS-Struktur, Design Tokens, Templates, Komponenten-Markup, JS-Interaktionen.
- Conditional Asset Loading in Abstimmung mit PHP-Ownern.
- Accessibility fuer Navigation, Graph, Tooltips, Forms, Share, Comments.

Dateien:

- `style.css`
- `assets/css/*`
- `assets/js/*`
- `inc/enqueue.php`
- `inc/runtime-assets.php`
- `inc/header-nav.php`
- Templates und `template-parts/*`

Verbote:

- Keine pauschale globale CSS-Erweiterung fuer ein lokales Feature.
- Kein direktes Editieren von `assets/js/d3-custom.min.js`; Quelle ist `_build-d3/src/d3-custom.js`.
- Keine CSS-Bewegung mit gleichzeitiger Selector-Umbenennung.

Abhaengigkeiten:

- Performance Agent fuer Ladepfade.
- Knowledge Graph Agent fuer Graph-Interaktion.
- Content Agent fuer Template-Semantik.

## Performance Agent

Zustaendigkeiten:

- Asset-Versionierung, Conditional Loading, Cache-Invalidierung, Cron/Rebuild-Kosten, Datenbankzugriffe.
- Graph-Build-Komplexitaet, Transient-/Option-Strategie, Tabellenmigrationen.
- CI-/Analyse-Kommandos.

Dateien:

- `inc/runtime-assets.php`
- `inc/enqueue.php`
- `inc/graph-api.php`
- `inc/glossary.php`
- `inc/link-preview.php`
- `inc/votes.php`
- `inc/privacy-maintenance.php`
- `composer.json`
- `.github/workflows/ci.yml`
- `scripts/*`

Verbote:

- Keine breiten SQL-Loeschungen oder Tabellenchecks in Hot Paths einfuehren.
- Keine neue Cron-Planung ohne Lock/Status/Failure-Pfad.
- Keine Build-Toolchain einfuehren ohne klaren Nutzen.

Abhaengigkeiten:

- Frontend Agent fuer Assets.
- Knowledge Graph Agent fuer Rebuilds.
- Analytics Agent fuer Messdaten.

## Analytics Agent

Zustaendigkeiten:

- SEO-Cockpit, Search Console, Koko Analytics, interne Linkgraph-Analyse, Leads/Attribution.
- Uebersetzung von Metriken in priorisierte Content-/SEO-Aufgaben.

Dateien:

- `inc/seo-cockpit/*`
- `inc/contact.php`
- `inc/forms/contact/*`
- `inc/contacts-admin.php`
- `inc/newsletter.php`
- `inc/forms/newsletter/*`
- `inc/newsletter-broadcast.php`

Verbote:

- Keine personenbezogenen Daten in Dokumentation oder Logs aufnehmen.
- Keine Credential-Konstanten lesen oder ausgeben.
- Keine Admin-Capability lockern.

Abhaengigkeiten:

- SEO Agent fuer Priorisierung.
- Performance Agent fuer Caches und Cron.
- Content Agent fuer redaktionelle Umsetzung.
