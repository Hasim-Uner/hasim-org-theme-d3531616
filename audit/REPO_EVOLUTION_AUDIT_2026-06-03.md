# Repo-Evolution Audit 2026-06-03

## Phase 1 - Audit

| Finding | Wo | Messwert | Ist vs. Soll |
|---|---|---:|---|
| `style.css`-Groesse ist nach Page-/Component-Splits aktualisiert. | `style.css`; `plans/repo-architektur-effizienz-2026.md:15`; `docs/AI_CONTEXT.md:44`; `CRO_PERFORMANCE_QUICK_WINS.md:3`; `docs/CSS_INVENTORY.md:9` | 3.326 Zeilen, 80.021 Byte | Soll einheitlich 3.326 Zeilen / 78 KB. Vorher dokumentiert waren 7.139, "7k+", 6.879 / 163 KB, 5.125, 4.801, 4.422, 4.266 und 3.396. |
| `graph.js`-Groesse ist in Optimierungsplan veraltet. | `assets/js/graph.js`; `plans/theme-optimierung-2026.md:36,70` | 1.158 Zeilen, 34.698 Byte | Soll 1.158 Zeilen. Plan nennt 872 Zeilen. |
| `hp-link-preview`-Ladebedingung ist in Docs veraltet. | `inc/enqueue.php:116`; `docs/ASSET_MATRIX.md:17`; `.github/copilot-instructions.md:80` | 5 Post Types, keine Pages | Code laedt auf `essay`, `note`, `post`, `glossar`, `dossier`. Docs nennen zusaetzlich `page`. |
| Votes-Ladebedingung ist in Copilot/CRO veraltet. | `inc/votes-api.php:128`; `.github/copilot-instructions.md:82`; `CRO_PERFORMANCE_QUICK_WINS.md:8` | 2 Single-Types, keine Archive | Code laedt nur auf Single `essay`/`note`. Docs/CRO nennen Archive. |
| Archivseiten-Entfernung ist erledigt. | `inc/seo-meta.php`; geloeschtes Legacy-Paket | 312.259 Byte Fallback-JPG entfernt | Live-OG-Fallback nutzt kein statisches Rosenbild mehr; letzter neutraler Fallback ist das WordPress-Site-Icon. |
| Archivseiten-Totgewicht ist entfernt. | geloeschtes Legacy-PHP/CSS/JS/Bildpaket | 907 PHP-Zeilen; 2.998 CSS-Zeilen / 72.496 Byte; 840 JS-Zeilen / 44.163 Byte; 3.086.056 Byte PNG | Inaktive Seite, CSS, JS, Rosen-PNG, Rosen-JPG und altes Audit wurden geloescht. |
| Reaktivierungs-Fatal-Risiko ist entfernt. | geloeschter Legacy-Enqueue-Block | 1 geloeschter Asset-Pfad | Der alte `filemtime()`-Pfad existiert nicht mehr im Runtime-Code. |
| Archivseiten-Dokumentation ist nachgezogen. | Plaene, Asset-Matrix, Hook-Matrix | technische Altpfad-Treffer: 0 | Asset-/Hook-Matrix und Plaene verweisen nicht mehr auf geloeschte Dateien. |
| Graph-Build-Komplexitaet ist reduziert, aber nicht erledigt. | `inc/graph-api.php:70-74,154-236,331-347,414-503` | `posts_per_page => -1`; Shared-Topic ueber Topic-Gruppen; Glossar-Matching ueber Regex-Chunks | Shared-Topic ist nicht mehr `O(posts^2)` ueber alle Post-Paare. Glossar-Matching scannt Posts chunkbasiert statt Begriff x Post. Vollstaendiges Post-Laden bleibt offen. |
| Graph-Rebuild ist im REST-/Render-Pfad asynchron gehalten. | `inc/graph-api.php:559-592,639-656`; `docs/AI_CONTEXT.md:45` | 0 synchrone Fallback-Rebuilds | Schema-/Legacy-Fallbacks markieren Payloads als stale, liefern vorhandene Daten und planen `hp_graph_rebuild_event`. |
| Mini-Graph-Quick-Win ist bereits umgesetzt. | `inc/graph-api.php:351-373`; `inc/mini-graph.php:61-86`; `CRO_PERFORMANCE_QUICK_WINS.md:12` | Neighbor-Map im Graph-Payload vorhanden | CRO nennt noch Edge-/Node-Scan pro Render. Code nutzt Neighbor-Map und scannt Edges nur als Fallback fuer alte Payloads. |
| Seeder sind kein Frontend-Hotpath, aber bleiben Runtime-Module. | `inc/manifest.php:30-31`; `inc/glossar-seed.php:18-35`; `inc/dossier-transhumanismus-seed.php:14-30` | 685 + 238 Zeilen | Code laeuft versioniert nur in `admin_init`. Offene Entscheidung: nach erfolgreichem Seed als Migration behalten oder aus Runtime-Manifest entfernen. |
| Root-Scratch-Rest ist erledigt. | geloeschter Root-Scratch | 2 Zeilen entfernt | Datei enthielt nur `<?php` und `// deleted`; keine Produktfunktion. |
| CSS-Split ist weiter offen, aber mehrere CRO-Punkte sind veraltet. | `docs/CSS_INVENTORY.md`; `inc/enqueue.php`; `inc/graph-api.php`; `CRO_PERFORMANCE_QUICK_WINS.md:3-6` | Mission, 404, Search, Topic, Archives, Single Editorial, Contact, Newsletter, Related, Post Nav, Legal und Wissensgraph bereits extrahiert | Soll: weitere Feature-/Komponenten-Extraktion nach Inventar. Ist: alte Quick-Wins behaupten noch globale Contact-/Graph-/Newsletter-CSS-Last. |
| Manifest und Architekturkarte stimmen im Kern ueberein. | `inc/manifest.php`; `docs/architecture.yml` | 31 Manifest-Eintraege; 27 Architekturmodule | Kein Neubau noetig. Architekturkarte modelliert Bootstrap, Runtime-Module, Seeder und D3-Build. |

## Phase 2 - Proposal

| Problem (Messwert) | Bezug zu Plan | Eingriff | erwarteter Gewinn | Risiko | betroffene Dateien | Rolle |
|---|---|---|---|---|---|---|
| Kontext-/Doku-Drift: `style.css` 3.326 statt 7.139/6.879/7k+/4.801/4.422/4.266/3.396, `graph.js` 1.158 statt 872, falsche Page-/Archiv-Ladebedingungen. | `repo-architektur-effizienz-2026.md` Phase 1/5; `theme-optimierung-2026.md` Statuspflege | Docs auf Ist-Stand patchen; CRO-Quick-Wins als erledigt/veraltet markieren oder korrigieren. | Weniger Fehlkontext fuer Agenten; keine Runtime-Aenderung. | niedrig | `docs/AI_CONTEXT.md`, `.github/copilot-instructions.md`, `docs/ASSET_MATRIX.md`, `docs/CSS_INVENTORY.md`, `CRO_PERFORMANCE_QUICK_WINS.md`, `plans/*.md` | Performance, Frontend, Knowledge Graph |
| Archivseiten-OG-Fallback blockiert Datei-Loeschung: 312.259 Byte statisches Rosenbild ist Live-Fallback. | Phase 6 Repo-Gewicht; neue Archivseiten-Entscheidung | Zuerst neutralen OG-Fallback festlegen: vorhandenes Site-Icon als letzter Fallback nutzen oder neues Brand-Bild einchecken und in `seo-meta.php` referenzieren. | Entfernt Altseiten-Namen aus Live-SEO-Pfad; macht anschliessendes Loeschen moeglich. | mittel, weil Social-Preview-Fallback betroffen ist | `inc/seo-meta.php`, ggf. neues Brand-Bild, `docs/architecture.yml` falls Asset neu | SEO |
| Archivseiten-Totgewicht: 907 PHP-Zeilen, 2.998 CSS-Zeilen, 840 JS-Zeilen, 3.086.056 Byte PNG. | Phase 6 Repo-Gewicht; neue Archivseiten-Entscheidung | Nach OG-Fallback: Dateien loeschen, Hooks/Asset-Matrix/alte Audit-Referenzen entfernen, technische Altseiten-Suchtreffer pruefen. | Entfernt inaktive Angriffs-/Fehlerflaeche und 3,5+ MB Repo-Gewicht. | mittel, weil Doku und SEO-Fallback sequenziert werden muessen | Legacy-Seitenpaket, `docs/HOOKS.md`, `docs/ASSET_MATRIX.md`, Plaene/Audits | Frontend, SEO, Performance |
| Root-Scratch-Rest: 2 Zeilen ohne Produktfunktion. | Phase 6 Repo-Gewicht | Datei loeschen. | Entfernt Root-Rauschen. | niedrig | geloeschter Root-Scratch | Performance |
| Graph-Build bleibt durch Vollscan teuer, trotz Cron. | `repo-architektur-effizienz-2026.md` Phase 4 | Post-/Term-Maps gezielter cachen und Glossar-Matches pro Post-Revision speichern. | Skalierung bei wachsendem Content; weniger Worst-Case-Latenz. | mittel | `inc/graph-api.php`, `inc/mini-graph.php`, `docs/AI_CONTEXT.md` | Knowledge Graph, Performance |
| Seeder laufen versioniert im Admin, bleiben aber Runtime-Manifest-Eintraege mit 923 Zeilen. | Phase 6 Repo-Gewicht / Content-Migration | Entscheiden: als dauerhafte Migration dokumentieren oder nach Seed-Abschluss aus Manifest entfernen und als Wartungsskript/Archiv behandeln. | Klarere Runtime-Grenze; weniger Admin-Bootstrap-Code. | mittel, weil Content-Erzeugung betroffen ist | `inc/manifest.php`, `inc/glossar-seed.php`, `inc/dossier-transhumanismus-seed.php`, ggf. Doku | Content, Performance |
| `style.css` bleibt groesster aktiver Kontextblock. | `repo-architektur-effizienz-2026.md` Phase 3 | Naechste CSS-Splits gemaess `docs/CSS_INVENTORY.md`: Glossary/Dossier/Mini-Graph. Keine Selector-Umbenennung. | Kleinere Reviews, weniger globales CSS, klarere Ownership. | mittel | `style.css`, neue/erweiterte `assets/css/pages/*`, `assets/css/features/*`, `inc/enqueue.php`, `docs/ASSET_MATRIX.md`, `docs/CSS_INVENTORY.md` | Frontend, Performance |

## Phase 3 - Ausgefuehrt

| Paket | Status | Dateien |
|---|---|---|
| Kontext-/Doku-Drift korrigieren | umgesetzt | `docs/AI_CONTEXT.md`, `.github/copilot-instructions.md`, `docs/ASSET_MATRIX.md`, `docs/CSS_INVENTORY.md`, `CRO_PERFORMANCE_QUICK_WINS.md`, `plans/repo-architektur-effizienz-2026.md`, `plans/theme-optimierung-2026.md` |
| Runtime-Cleanup Archivseite | umgesetzt | `inc/seo-meta.php`, geloeschtes Legacy-PHP/CSS/JS/Bildpaket, `docs/HOOKS.md`, `docs/ASSET_MATRIX.md`, `docs/CSS_INVENTORY.md`, `CRO_PERFORMANCE_QUICK_WINS.md`, `plans/*.md` |
| Root-Scratch entfernen | umgesetzt | geloeschter Root-Scratch |
| Graph Shared-Topic optimieren | umgesetzt | `inc/graph-api.php`, `CRO_PERFORMANCE_QUICK_WINS.md`, `plans/repo-architektur-effizienz-2026.md` |
| Graph synchrone Fallback-Rebuilds entfernen | umgesetzt | `inc/graph-api.php`, `docs/AI_CONTEXT.md`, `audit/REPO_EVOLUTION_AUDIT_2026-06-03.md` |
| Graph Glossar-Matching optimieren | umgesetzt | `inc/graph-api.php`, `docs/AI_CONTEXT.md`, `CRO_PERFORMANCE_QUICK_WINS.md`, `plans/repo-architektur-effizienz-2026.md` |
| Mission-CSS splitten | umgesetzt | `style.css`, `assets/css/pages/mission.css`, `inc/enqueue.php`, `docs/ASSET_MATRIX.md`, `docs/CSS_INVENTORY.md` |
| 404/Search/Topic-CSS splitten | umgesetzt | `style.css`, `assets/css/pages/error.css`, `assets/css/pages/search.css`, `assets/css/pages/topic-archive.css`, `inc/enqueue.php`, `docs/ASSET_MATRIX.md`, `docs/CSS_INVENTORY.md` |
| Related/Post-Nav-CSS splitten | umgesetzt | `style.css`, `assets/css/components/related.css`, `assets/css/components/post-nav.css`, `inc/enqueue.php`, `docs/ASSET_MATRIX.md`, `docs/CSS_INVENTORY.md` |
| Single-Editorial-CSS splitten | umgesetzt | `style.css`, `assets/css/pages/single-editorial.css`, `inc/enqueue.php`, `docs/ASSET_MATRIX.md`, `docs/CSS_INVENTORY.md` |
| Archive-CSS splitten | umgesetzt | `style.css`, `assets/css/pages/archives.css`, `inc/enqueue.php`, `docs/ASSET_MATRIX.md`, `docs/CSS_INVENTORY.md` |

## Stop

Runtime-Cleanup ausgefuehrt: OG-Fallback neutralisiert, inaktives Archivseitenpaket geloescht, Root-Scratch entfernt, Shared-Topic-Graph-Build optimiert, synchrone Graph-Fallback-Rebuilds entfernt, Glossar-Matching chunkbasiert optimiert und Mission/404/Search/Topic/Archives/Single/Related/Post-Nav-CSS bedingt ausgelagert. Freigabe noetig fuer die verbleibenden Umsetzungspakete: Graph-Vollscan-Caching, Seeder-Entscheidung, weitere CSS-Splits.
