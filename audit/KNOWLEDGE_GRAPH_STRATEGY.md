# Knowledge Graph Strategy

Stand: 2026-06-02

## Antwort

Ja, aus dem Repository kann ein expliziter Knowledge Graph erzeugt werden. Die Grundlagen sind bereits vorhanden:

- `essay` und `note` als redaktionelle Dokumente.
- `glossar` als Begriffs-Entitaeten.
- `topic` als thematische Cluster.
- `dossier` als kuratierte Pfade.
- `graph-api.php` als REST-/Cache-Schicht.
- `mini-graph.php` als lokaler Nachbarschaftsrenderer.
- JSON-LD in `seo-schema.php` als externe semantische Repraesentation.

## Datenmodell

### Nodes

| Node-Typ | Quelle | ID-Konvention | Zweck |
|---|---|---|---|
| `person` | Site-Konstanten/Schema | `home/#person` | Autor/Herausgeber |
| `organization` | Site-Kontext | `home/#organization` | Publisher |
| `website` | Site-Kontext | `home/#website` | Publikationssystem |
| `essay` | CPT `essay` | `essay_{post_id}` und URL `#article` | Longform-Analyse |
| `note` | CPT `note` | `note_{post_id}` und URL `#article` | Kurzformat |
| `glossar` | CPT `glossar` | `glossar_{post_id}` und URL `#term` | DefinedTerm |
| `topic` | Taxonomie `topic` | `topic_{term_id}` und URL `#topic` | Themencluster |
| `dossier` | CPT `dossier` | `dossier_{post_id}` und URL `#dossier` | kuratierter Wissensknoten |
| `source` | Dossier/Glossar-Quellen | `source_{hash}` | zitierte Quelle, optional Phase 2 |

### Edge-Typen

| Edge | Quelle | Bedeutung |
|---|---|---|
| `topic_membership` | WP-Term-Zuordnung | Inhalt gehoert zu Topic |
| `shared_topic` | abgeleitet aus Term-Index | zwei Inhalte teilen Themenfeld |
| `glossar_in_content` | Term-Index/Textscan | Inhalt verwendet Begriff |
| `glossar_related` | `_hp_glossar_verwandt` | Begriff ist mit Begriff verwandt |
| `dossier_has_part` | `_hp_dossier_leseplan` | Dossier enthaelt Essay/Note in Reihenfolge |
| `dossier_mentions_term` | `_hp_dossier_begriffe` | Dossier behandelt Begriff |
| `dossier_links_to` | interne Links im Dossier-Content | Dossier verweist redaktionell auf bestehenden Graph-Knoten |
| `dossier_in_topic` | Term-Zuordnung | Dossier gehoert zu Topic |
| `post_in_dossier` | Reverse aus Leseplan | Essay/Note ist Teil eines Dossiers |
| `term_in_dossier` | Reverse aus Begriffsapparat | Begriff ist Teil eines Dossiers |
| `cites` | Quellenfelder | Dossier/Glossar zitiert Quelle |

## Schema.org Mapping

| Intern | Schema.org |
|---|---|
| Person | `Person` |
| Organization | `Organization` |
| Website | `WebSite` |
| Essay | `ScholarlyArticle` |
| Note | `BlogPosting` |
| Glossar | `DefinedTerm` |
| Glossar-Archiv | `DefinedTermSet` |
| Dossier | `Article` oder `CreativeWork` mit `hasPart`, `mentions`, `citation` |
| Topic | `Thing` oder `DefinedTerm` als `about` einer `CollectionPage` |
| Topic-Archiv | `CollectionPage` mit `mainEntity: ItemList` |
| Breadcrumbs | `BreadcrumbList` |

## SEO-Nutzen

- Staerkere Entity Consolidation durch stabile `@id`.
- Besseres Topical Authority Signal durch Topic-Pillar, Glossar und Dossier-Hubs.
- Richere interne Linkstruktur durch semantisch gewichtete Links.
- Bessere AI-Retrieval-Faehigkeit durch `llms.txt`, Dossiers und explizite Beziehungen.
- Mehr Crawl-Effizienz, weil Graph/Schema/Sitemap dieselben Prioritaeten abbilden.
- Hoehere Zitierfaehigkeit durch Dossier-Zitationen und Quellenmodell.

## Implementierungsplan

### Phase 1: Architekturvertrag

- `docs/architecture.yml` als kanonische Modulkarte pflegen.
- Entity-ID-Konvention in `docs/architecture.yml` und `docs/AGENTS.md` festschreiben.
- `docs/change-impact.yml` fuer alle Graph-/Schema-Dateien nutzen.

### Phase 2: Graph-Datenmodell erweitern

- Dossier-Nodes in `hp_graph_build_data()` aufnehmen. Umgesetzt fuer sichtbare Dossiers.
- Edges `dossier_has_part`, `dossier_mentions_term`, `dossier_links_to`, `dossier_in_topic` erzeugen. `dossier_has_part`, `dossier_mentions_term` und `dossier_links_to` sind umgesetzt; `dossier_in_topic` laeuft ueber `topic_membership`.
- Glossar-Verwandtschaft als `glossar_related` aufnehmen.
- Edge-Metadaten um `source`, `confidence`, `order` erweitern.

### Phase 3: Gemeinsamen Term-Index bauen

- Eine Funktion fuer Glossar-Titel, Synonyme, IDs, URLs, Kurzdefinitionen.
- Nutzung durch Auto-Linking, zentrale Begriffe, Graph-Build und Link-Preview.
- Cache-Version `hp_glossar_term_index_version` statt breiter SQL-Loeschung.

### Phase 4: JSON-LD konsolidieren

- Stabile `@id` fuer Article/DefinedTerm/Dossier/Topic.
- Dossier `hasPart` mit `@id` der Zielartikel.
- Glossar `alternateName` und `seeAlso` fuer verwandte Begriffe.
- Topic-Pages mit `about` und `mainEntity`.

### Phase 5: Agenten- und SEO-Cockpit-Integration

- SEO-Cockpit-Insights als maschinenlesbares Exportformat.
- Interne Linktypen getrennt messen: nav, topic, dossier, glossary, graph, related.
- Change-Impact-Map nutzen, um Agenten vor Aenderungen an Entity-Systemen zu warnen.
