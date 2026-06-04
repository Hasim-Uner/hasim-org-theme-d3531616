# Repository Audit

Stand: 2026-06-02

## Executive Summary

Das Theme ist fuer ein WordPress-Child-Theme architektonisch bereits ueberdurchschnittlich gut strukturiert. `functions.php` ist Bootstrap-only, `inc/bootstrap.php` validiert ein explizites Manifest, Kernmodule sind fachlich getrennt, CI prueft PHP-Lint, Manifest und PHPStan, und es existieren bereits `docs/ARCHITECTURE.md`, `docs/AI_CONTEXT.md`, `docs/ASSET_MATRIX.md` und `docs/CSS_INVENTORY.md`.

Staerken:

- Expliziter Bootstrap-Flow ueber `inc/manifest.php` statt impliziter Include-Wildcards.
- Klare fachliche Systeme: SEO, Knowledge Graph, Glossar, Dossier, Newsletter, Kontakt, Votes, Kommentare.
- Starkes SEO-Fundament: native Meta-Ausgabe, Canonicals, JSON-LD, Breadcrumbs, Sitemap-Hygiene, Robots-Regeln, Last-Modified/ETag.
- Knowledge-System ist schon als Produkt gedacht: Topics, Glossar, Dossiers, Mini-Graph, Wissensgraph, zentrale Begriffe.
- Agenten-Kontext ist bereits reduziert durch `docs/AI_CONTEXT.md` und `docs/CSS_INVENTORY.md`.
- Conditional Asset Loading ist fuer Graph, Kontakt, Front Page, Legal, Singles, Votes und Newsletter weitgehend vorhanden.

Schwaechen:

- Architektur ist teils dokumentiert, aber nicht vollstaendig maschinenlesbar. Vor diesem Audit fehlten `docs/architecture.yml`, Ownership-Modell und Change-Impact-Map.
- `style.css` bleibt ein grosser globaler Besitzbereich. Das erhoeht Review-, Agenten- und Regressionskosten.
- Mehrere Editor-Panels sind Inline-JS in PHP-Strings. Das ist schwer zu testen, schwer zu diffen und fuer Agenten fehleranfaellig.
- Knowledge-Graph-Edges werden teilweise aus Textscans und Topic-Paarvergleichen abgeleitet. Das skaliert schlechter als ein explizites Relationsmodell.
- REST-Namespace ist inkonsistent: Graph/Link-Preview nutzen `hp/v1`, Votes nutzt `hasim-org/v1`.
- Einige einmalige Migrationen laufen weiterhin auf `init` und bleiben als Laufzeitballast sichtbar.

Risiken:

- Sicherheits-/Operationsrisiko: `inc/contact-local.php` existiert lokal mit Credential-Konstanten. Die Datei ist in `.gitignore`, darf aber von Agenten nicht gelesen, zitiert oder in Diffs beruehrt werden.
- Performance-Risiko: `inc/votes.php` prueft Tabellenexistenz auf jedem `init` via `SHOW TABLES`. Das ist fuer kleine Sites tolerierbar, aber architektonisch falsch.
- SEO-Risiko: Dossiers, Glossar und Graph nutzen semantische Beziehungen, aber nicht alle Beziehungen sind als stabile `@id`-Knoten modelliert.
- Agenten-Risiko: Ownership ist ueber Dateinamen ableitbar, aber nicht hart genug dokumentiert. Agenten koennen versehentlich SEO-, Graph- und Frontend-Seiteneffekte mischen.
- Caching-Risiko: Link-Preview verwendet `hp_glossar_version` fuer Preview-Cache-Keys. Das koppelt Preview-Invalidierung an Glossar-Autolinking.

Technische Schulden:

- `style.css` als globaler Sammelpunkt.
- Inline-Editor-JS in `inc/meta-fields.php`, `inc/seo-meta.php`, `inc/glossary.php`, `inc/dossier.php`.
- Manuelle ID-Listen in Dossier-Meta (`_hp_dossier_leseplan`, `_hp_dossier_begriffe`) statt typisierte Relation-Storage.
- Graph-Build mit `array_intersect()` ueber Post-Paare und Regex-Suche ueber alle Glossarvarianten.
- SEO-Cockpit ist leistungsstark, aber mit grossen Dateien (`seo-cockpit-ui.php`, `seo-cockpit-insights.php`) schwer zu reviewen.
- Seeder/Migrationen bleiben dauerhaft im Manifest.

SEO-Risiken:

- Dossier-Schema nutzt `hasPart`, `mentions`, `citation`; sehr stark, aber ohne zentrale Entity-ID-Policy koennen Knoten fragmentieren.
- Glossar-DefinedTerm nutzt `inDefinedTermSet`, aber verwandte Begriffe, Synonyme und Sprachvarianten sind nicht vollstaendig in JSON-LD modelliert.
- Topic-Pillar-Seiten sind stark, aber Dossiers sind nicht Teil des Graph-REST-Payloads und fehlen damit im visuellen Wissensnetz.
- Search Console/SEO-Cockpit ist adminseitig vorhanden, aber die Erkenntnisse sind noch nicht als maschinenlesbare Aufgaben oder Change-Impact-Signale angebunden.

Agenten-Risiken:

- Ohne Ownership-Datei ist unklar, welcher Agent welche Dateien anfassen darf.
- Lokale Secrets koennen von Agenten beim breiten `rg` oder `sed` sichtbar werden.
- Fehlende generierte Hooks-/REST-Dokumentation zwingt Agenten zu Regex-Suchen.
- Change-Impact war bisher implizit. Kleine Aenderungen in `seo-schema.php`, `graph-api.php`, `glossary.php` oder `dossier.php` koennen grosse semantische Effekte haben.

## Architekturuebersicht

### Bootstrap Flow

1. WordPress laedt `functions.php`.
2. `functions.php` prueft `ABSPATH` und laedt ausschliesslich `inc/bootstrap.php`.
3. `inc/bootstrap.php` laedt `inc/manifest.php`, validiert Array und Dateiexistenz, und inkludiert jedes Modul in Reihenfolge.
4. `inc/manifest.php` ist die kanonische Laufzeitreihenfolge. Fruehe Module stellen Helfer bereit (`helpers.php`, `feature-flags.php`, `runtime-assets.php`), danach folgen Content-Typen, SEO, Knowledge-Systeme, Forms, Graph und Engagement.

### Modulstruktur

- Core: `helpers.php`, `feature-flags.php`, `runtime-assets.php`, `enqueue.php`, `generatepress-compat.php`, `header-nav.php`.
- Content Model: `post-types.php`, `taxonomies.php`, `glossary.php`, `dossier.php`, Seeder.
- SEO: `seo-meta.php`, `seo-schema.php`, `seo-hygiene.php`, `sitemap.php`, `breadcrumbs.php`, `llms-txt.php`, `seo-cockpit/*`.
- Knowledge Graph: `graph-api.php`, `mini-graph.php`, Glossar-/Dossier-Beziehungen und Topic-Taxonomie.
- Forms/Admin: `contact.php`, `forms/contact/*`, `contacts-admin.php`, `newsletter.php`, `forms/newsletter/*`, `newsletter-broadcast.php`, `privacy-maintenance.php`.
- Engagement: `comments.php`, `votes.php`, `votes-api.php`.
- Templates: `single-essay.php`, `single-note.php`, `single-glossar.php`, `single-dossier.php`, `taxonomy-topic.php`, archive/page templates.

### Hook-System

Hooks sind klassisch WordPress-nativ verteilt. Die wichtigsten Hook-Zonen:

- `init`: CPTs, Taxonomie, Meta-Felder, Migrationen, Tabelleninstallationen, llms.txt, SEO-Cockpit-Cron.
- `wp_enqueue_scripts`: globale und bedingte Assets, Graph-Assets, Votes, Heartbeat-Throttle.
- `wp_head`: SEO-Meta, Organization/WebSite, Article/DefinedTerm/Dossier/Archive/Mission JSON-LD, hreflang, rel=me, RSS, Fonts, Breadcrumbs.
- `template_redirect`: SEO-Redirects, Author-/Attachment-Handling, Dossier-404, Last-Modified/ETag.
- `rest_api_init`: Graph, Link-Preview, Votes.
- `save_post*`/`transition_post_status`: Graph-, Glossar-, Link-Preview-, SEO-Cockpit- und Broadcast-Invalidierungen.
- `admin_post_*`: Kontakt, Newsletter, SEO-Cockpit, Exporte.

### REST-System

- `GET /wp-json/hp/v1/graph`: liefert kompilierten Wissensgraphen.
- `GET /wp-json/hp/v1/link-preview`: liefert interne Link-Preview-Daten.
- `POST /wp-json/hasim-org/v1/vote`: verarbeitet Likes/Dislikes.

Bewertung: Graph und Link-Preview sind sauber oeffentlich lesbar. Vote-Route ist per Nonce gesichert, aber Namespace und Nonce-Verwendung sind inkonsistent zum Markup.

### SEO-System

- Meta und Canonical: `inc/seo-meta.php`.
- JSON-LD: `inc/seo-schema.php` plus `inc/breadcrumbs.php`.
- Crawl- und Index-Hygiene: `inc/seo-hygiene.php`, `inc/sitemap.php`, `robots.txt`.
- Monitoring/Insights: `inc/seo-cockpit/*` mit Search Console, Koko Analytics, interner Linkanalyse, Diagnostics, Quick Wins.

### Knowledge-Graph-System

- Datenquellen: `essay`, `note`, `glossar`, `topic`; Dossier-Beziehungen in Templates/Schema, aber noch nicht im Graph-Payload.
- Edge-Typen: `topic_membership`, `shared_topic`, `glossar_in_content`.
- Cache: persistent in `hp_graph_payload`, Status in `hp_graph_status`, Version in `hp_graph_version`.
- Rebuild: async per `hp_graph_rebuild_event` nach Save/Delete/Topic-Events.
- Mini-Graph: liest kompiliertes Payload und rendert ein statisches SVG.

### Asset-System

- Globale Assets: Parent/GeneratePress CSS, `style.css`, `nav.js`.
- Bedingt: Front Page CSS, Kontakt CSS, Newsletter CSS, Legal CSS, Graph CSS/JS, Single JS, Link-Preview JS, Votes CSS/JS.
- Versionierung: `hp_asset_version()` via `filemtime()` mit Theme-Version-Fallback.
- Script-Strategie: `hp_enqueue_deferred_script()` und WP 6.3 `strategy => defer`.

### Caching-System

- Graph: Optionen `hp_graph_payload`, `hp_graph_status`, `hp_graph_version`, `hp_graph_last_error`.
- Glossar Auto-Link: Transients `hp_gl_{post}_v{hp_glossar_version}` plus breite SQL-Aufraeumung.
- Link-Preview: Transients `hp_lp_{post}_v{hp_glossar_version}`.
- SEO-Cockpit: versionierte Transient-Keys ueber `nexus_get_seo_cockpit_cache_key()`, Sync-Lock, Snapshot-Caches.
- Forms: Flash-Transients und Rate-Limit-Transients.
- Comments: Rate-Limit-Transients.

## Top 20 Hebel

| Prio | Hebel | Impact | Aufwand | Risiko | SEO-Wirkung | Wartbarkeitsgewinn |
|---|---|---:|---:|---:|---:|---:|
| P1 | `docs/architecture.yml`, `docs/AGENTS.md`, `docs/change-impact.yml` pflegen | Hoch | Niedrig | Niedrig | Mittel | Hoch |
| P1 | Hooks/REST-Doku generierbar machen | Hoch | Niedrig | Niedrig | Mittel | Hoch |
| P1 | Lokale Secrets als Agenten-Tabu dokumentieren und optional Secret-Scan in CI | Hoch | Niedrig | Niedrig | Niedrig | Hoch |
| P1 | Votes-Tabellencheck von jedem `init` entfernen und versionierte DB-Migration nutzen | Hoch | Niedrig | Mittel | Niedrig | Hoch |
| P1 | Vote-Nonce-Konzept vereinheitlichen (`hp_vote_nonce` vs. postbezogener Markup-Nonce) | Hoch | Niedrig | Mittel | Niedrig | Mittel |
| P1 | Link-Preview von `hp_glossar_version` entkoppeln | Hoch | Niedrig | Niedrig | Mittel | Hoch |
| P1 | Dossiers in Graph-Payload aufnehmen | Hoch | Mittel | Mittel | Hoch | Hoch |
| P1 | Stabile Entity-ID-Policy fuer JSON-LD definieren | Hoch | Mittel | Mittel | Hoch | Hoch |
| P1 | Graph-Edge-Build ueber Term-Index statt Post-Paarvergleich | Hoch | Mittel | Mittel | Mittel | Hoch |
| P1 | Glossar-Term-Index fuer Auto-Link, Zentralbegriffe und Graph teilen (umgesetzt 2026-06-04) | Hoch | Mittel | Mittel | Hoch | Hoch |
| P2 | Inline-Editor-JS in dedizierte Editor-Assets auslagern | Mittel | Mittel | Niedrig | Niedrig | Hoch |
| P2 | Restliche Feature-CSS aus `style.css` nach Ownern extrahieren | Mittel | Mittel | Mittel | Mittel | Hoch |
| P2 | Dossier-Relationen typisieren statt comma-separated IDs | Hoch | Hoch | Mittel | Hoch | Hoch |
| P2 | SEO-Cockpit grosses UI/Insights-Modul intern aufteilen | Mittel | Mittel | Mittel | Mittel | Hoch |
| P2 | CI um Architekturgenerator-Drift-Check erweitern | Mittel | Niedrig | Niedrig | Mittel | Hoch |
| P2 | Schema.org fuer Glossar-Synonyme, Sprachvarianten und verwandte Begriffe erweitern | Hoch | Mittel | Mittel | Hoch | Mittel |
| P2 | Topic-Pillar-Metriken aus SEO-Cockpit mit Change-Impact verbinden | Mittel | Mittel | Mittel | Hoch | Mittel |
| P3 | Seeder aus Produktivmanifest in Admin-/CLI-Migrationspfad verschieben | Mittel | Mittel | Mittel | Niedrig | Mittel |
| P3 | Archived Diaspora-Seite entweder dokumentiert kapseln oder aus aktiver Agentenkarte ausblenden | Niedrig | Niedrig | Niedrig | Niedrig | Mittel |
| P3 | Root-`package.json` nur einfuehren, wenn CSS/Editor-Asset-Build wirklich kommt | Niedrig | Mittel | Niedrig | Niedrig | Mittel |
