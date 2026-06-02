# Priority Actions

Stand: 2026-06-02

Nur die 10 Massnahmen mit dem hoechsten ROI. Streng sortiert nach erwarteter Wirkung auf SEO, Skalierung, Agentenfaehigkeit und Wartbarkeit im Verhaeltnis zum Aufwand.

## 1. Dossiers in den Knowledge-Graph aufnehmen

Status: umgesetzt am 2026-06-03 fuer sichtbare Dossier-Nodes, Leseplan-Edges, Begriffsapparat-Edges, UI-Filter, Legende und Detail-Beziehungskontext.

Problem: `graph-api.php` modellierte Essays, Notizen, Glossar und Topics, aber nicht Dossiers. Damit fehlten die staerksten kuratierten Wissensknoten im visuellen und maschinenlesbaren Graph.

Nutzen: Dossiers werden zentrale Hub-Knoten mit Leseplan-, Begriffsapparat- und Topic-Beziehungen. Das verbindet Content-Strategie, interne Verlinkung und Entity SEO.

Risiko: Mittel. Graph-Payload, Frontend-Filter und Mini-Graph muessen mit dem neuen Knotentyp umgehen.

Aufwand: Mittel.

Betroffene Dateien:

- `inc/graph-api.php`
- `inc/dossier.php`
- `assets/js/graph.js`
- `assets/css/pages/wissensgraph.css`
- `page-wissensgraph.php`
- `inc/seo-schema.php`
- `docs/architecture.yml`
- `docs/change-impact.yml`

Erwarteter SEO Impact: Sehr hoch.

Erwarteter Wartbarkeitsgewinn: Hoch.

## 2. Stabile Entity-ID-Policy in JSON-LD durchsetzen

Problem: Person, Organization, WebSite und Glossar nutzen bereits IDs, aber Article-, Dossier- und Topic-IDs sind nicht konsequent als zentrale Architekturregel umgesetzt.

Nutzen: Suchmaschinen und AI-Systeme koennen Entitaeten stabil zusammenfuehren. Schema, Graph und interne Links sprechen dieselbe Entity-Sprache.

Risiko: Mittel. Falsche ID-Aenderungen koennen strukturierte Daten fragmentieren.

Aufwand: Mittel.

Betroffene Dateien:

- `inc/seo-schema.php`
- `inc/breadcrumbs.php`
- `inc/seo-meta.php`
- `inc/glossary.php`
- `inc/dossier.php`
- `taxonomy-topic.php`
- `docs/architecture.yml`

Erwarteter SEO Impact: Sehr hoch.

Erwarteter Wartbarkeitsgewinn: Hoch.

## 3. Glossar-Term-Index zentralisieren

Problem: Glossar-Autolinking, zentrale Begriffe und Graph-Build scannen Titel/Synonyme getrennt. Dadurch entstehen doppelte Logik, abweichende Treffer und unnoetige Laufzeitkosten.

Nutzen: Ein gemeinsamer Index fuer Titel, Synonyme, URL, Kurzdefinition, Sprache und Ziel-ID macht Auto-Linking, Graph-Edges und SEO-Schema konsistenter.

Risiko: Mittel. Autolinking ist sichtbar und darf keine falschen Links erzeugen.

Aufwand: Mittel.

Betroffene Dateien:

- `inc/glossary.php`
- `inc/graph-api.php`
- `inc/link-preview.php`
- `single-essay.php`
- `single-glossar.php`
- `inc/seo-schema.php`

Erwarteter SEO Impact: Hoch.

Erwarteter Wartbarkeitsgewinn: Sehr hoch.

## 4. Link-Preview-Cache von `hp_glossar_version` entkoppeln

Problem: `inc/link-preview.php` nutzt `hp_glossar_version` fuer Preview-Cache-Keys. Das koppelt interne Link-Previews unnoetig an Glossar-Autolink-Invalidierung.

Nutzen: Saubere Cache-Domaenen: Glossar-Autolinks, Link-Previews und Graph koennen getrennt invalidiert werden. Weniger Cache-Rauschen bei Content-Aenderungen.

Risiko: Niedrig. Der bestehende Cache kann kompatibel auslaufen.

Aufwand: Niedrig.

Betroffene Dateien:

- `inc/link-preview.php`
- `inc/glossary.php`
- `inc/graph-api.php`
- `docs/change-impact.yml`

Erwarteter SEO Impact: Mittel.

Erwarteter Wartbarkeitsgewinn: Hoch.

## 5. Vote-System-Datenbankmigration reparieren

Problem: `inc/votes.php` prueft Tabellenexistenz auf jedem `init` per `SHOW TABLES`. Das ist ein Hot-Path-Datenbankzugriff und architektonisch kein skalierbares Migrationsmodell.

Nutzen: Versionierte DB-Migration per Option reduziert Request-Kosten und macht das Vote-System vorhersehbarer.

Risiko: Mittel. Bestehende Installationen duerfen die Tabelle nicht verlieren.

Aufwand: Niedrig bis Mittel.

Betroffene Dateien:

- `inc/votes.php`
- `inc/votes-api.php`
- `plans/like-dislike-system.md`
- `docs/change-impact.yml`

Erwarteter SEO Impact: Niedrig.

Erwarteter Wartbarkeitsgewinn: Hoch.

## 6. Vote-Nonce und REST-Namespace vereinheitlichen

Problem: Vote-Markup erzeugt eine postbezogene Nonce, die REST-API validiert aber `hp_vote_nonce`. Zusaetzlich nutzt Votes `hasim-org/v1`, waehrend Graph und Link-Preview `hp/v1` nutzen.

Nutzen: Weniger Fehlersuche, klareres REST-System, konsistente Agentenkarte und stabilere Frontend/API-Kopplung.

Risiko: Mittel. Bestehendes Frontend-JS und lokalisierte Daten muessen synchron angepasst werden.

Aufwand: Niedrig.

Betroffene Dateien:

- `inc/votes.php`
- `inc/votes-api.php`
- `assets/js/votes.js`
- `docs/architecture.yml`
- `docs/REST_ROUTES.md`

Erwarteter SEO Impact: Niedrig.

Erwarteter Wartbarkeitsgewinn: Hoch.

## 7. Hooks- und REST-Dokumentation generieren und in CI pruefen

Problem: Hooks und REST-Routen sind im Code verteilt. Agenten muessen Side Effects per `rg` rekonstruieren und koennen Registrierungen uebersehen.

Nutzen: Reproduzierbare `docs/HOOKS.md` und `docs/REST_ROUTES.md` machen Seiteneffekte sichtbar. Ein CI-Drift-Check verhindert veraltete Dokumentation.

Risiko: Niedrig. Der Generator muss als Referenz, nicht als perfekter PHP-Parser verstanden werden.

Aufwand: Niedrig.

Betroffene Dateien:

- `scripts/generate-wp-docs.php`
- `docs/HOOKS.md`
- `docs/REST_ROUTES.md`
- `.github/workflows/ci.yml`
- `composer.json`

Erwarteter SEO Impact: Mittel.

Erwarteter Wartbarkeitsgewinn: Sehr hoch.

## 8. Inline-Editor-JS in dedizierte Editor-Assets auslagern

Problem: Mehrere Gutenberg-Panels liegen als Inline-JS in PHP-Strings. Das ist schwer zu lesen, zu diffen, zu testen und fuer Agenten riskant.

Nutzen: Editor-Logik wird cachebar, reviewbar und nach Ownern trennbar. PHP-Module werden kuerzer und fachlicher.

Risiko: Niedrig bis Mittel. Asset-Enqueue und WP-Editor-Abhaengigkeiten muessen exakt bleiben.

Aufwand: Mittel.

Betroffene Dateien:

- `inc/meta-fields.php`
- `inc/seo-meta.php`
- `inc/glossary.php`
- `inc/dossier.php`
- `inc/enqueue.php`
- `assets/js/editor/*`
- `docs/ASSET_MATRIX.md`

Erwarteter SEO Impact: Niedrig.

Erwarteter Wartbarkeitsgewinn: Hoch.

## 9. Feature-CSS aus `style.css` nach Ownern extrahieren

Problem: `style.css` ist weiterhin ein grosser globaler Besitzbereich. Glossar, Dossier, Mini-Graph, Search, Topic und Single-Layouts teilen denselben Kontext.

Nutzen: Kleinere CSS-Dateien, bessere Agentenfaehigkeit, weniger Regressionsrisiko und klarere Conditional Loading-Pfade.

Risiko: Mittel. CSS-Extraktion kann visuelle Regressionen erzeugen, wenn Selektoren oder Reihenfolge versehentlich geaendert werden.

Aufwand: Mittel.

Betroffene Dateien:

- `style.css`
- `assets/css/features/glossary.css`
- `assets/css/features/dossier.css`
- `assets/css/features/mini-graph.css`
- `assets/css/pages/search.css`
- `assets/css/pages/topic-archive.css`
- `inc/enqueue.php`
- `inc/glossary.php`
- `inc/dossier.php`
- `docs/CSS_INVENTORY.md`
- `docs/ASSET_MATRIX.md`

Erwarteter SEO Impact: Mittel.

Erwarteter Wartbarkeitsgewinn: Sehr hoch.

## 10. SEO-Cockpit-Insights maschinenlesbar exportieren

Problem: SEO-Cockpit erzeugt Quick Wins, Problem Pages, Query Movers und interne Linkkontexte, aber diese Erkenntnisse sind noch nicht als Agenten-Input standardisiert.

Nutzen: Agents koennen SEO-Arbeit priorisieren, ohne UI-Screens interpretieren zu muessen. Jede Massnahme kann direkt auf betroffene Systeme aus `change-impact.yml` gemappt werden.

Risiko: Mittel. Keine personenbezogenen oder geheimen Daten duerfen in Exporte gelangen.

Aufwand: Mittel.

Betroffene Dateien:

- `inc/seo-cockpit/seo-cockpit-insights.php`
- `inc/seo-cockpit/seo-cockpit-links.php`
- `inc/seo-cockpit/seo-cockpit-sync.php`
- `inc/seo-cockpit/seo-cockpit-ui.php`
- `docs/change-impact.yml`

Erwarteter SEO Impact: Hoch.

Erwarteter Wartbarkeitsgewinn: Mittel.
