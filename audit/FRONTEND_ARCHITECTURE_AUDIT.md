# Frontend Architecture Audit

Stand: 2026-06-02

Fokus: nur Massnahmen mit hohem ROI.

## Befund

Das Frontend ist kein unkontrollierter Asset-Ball mehr. Globale Assets sind begrenzt, mehrere Page-/Feature-CSS-Dateien sind bereits extrahiert, und JavaScript ist nach Features getrennt. Der Haupt-ROI liegt in weiterer Ownership-Schaerfung, nicht in einem grossen Build-System.

## CSS-Struktur

Staerken:

- `docs/CSS_INVENTORY.md` kartiert `style.css` nach Bereichen.
- Page-/Component-CSS fuer Front Page, Mission, Kontakt, Legal, 404, Search, Topic, Archives, Single Editorial, Related, Post Nav und Wissensgraph ist bereits ausgelagert.
- Newsletter und Votes haben eigene CSS-Dateien.
- Design Tokens sind im Theme vorhanden (`--hj-*`, `--wp-*`).

Risiken:

- `style.css` bleibt gross und besitzt mehrere Systeme zugleich: Header, Prose, Glossar, Dossier und Mini-Graph.
- Agenten muessen bei Frontend-Aenderungen oft in einem grossen globalen File operieren.
- Feature-Regressionsrisiko ist hoch, wenn CSS bewegt und Verhalten geaendert wird.

Hoher ROI:

1. Feature-CSS extrahieren: Glossar, Dossier, Mini-Graph.
2. Danach Layout/Component-CSS extrahieren: Header, Footer, Prose, TOC.

## Design Tokens

Staerken:

- Es gibt zentrale Tokenbereiche im CSS.
- Wissensplattform-spezifische Graph-/Begriffsfarben sind schon getrennt.

Risiken:

- Tokens sind CSS-seitig vorhanden, aber nicht maschinenlesbar dokumentiert.
- Agenten koennen neue Farben einfuehren, statt vorhandene Tokens zu nutzen.

Hoher ROI:

- In `docs/architecture.yml` Asset-/Owner-Bezug pflegen.
- Optional spaeter `docs/design-tokens.yml` erzeugen, aber erst wenn CSS-Split startet.

## Komponentenstruktur

Staerken:

- Template-Komponenten sind semantisch klar: Essay Hero, TOC, Mini-Graph, Dossier-Leseplan, Glossar-Begriff, Topic-Pillar.
- JS ist featureweise getrennt: nav, journal-single, link-preview, graph, votes.

Risiken:

- Einige Komponenten sind nur durch CSS-Klassen und Template-Markup definiert, nicht als dokumentierte Komponenten.
- Dossier Cite Box enthaelt Inline-SVG und erwartet JS-Verhalten in `journal-single.js`/globaler Logik.
- Link Preview uebernimmt Glossar-Chip-Interaktion; diese Ownership sollte explizit bleiben.

Hoher ROI:

- `docs/change-impact.yml` fuer Komponenten pflegen.
- Bei naechster Frontend-Aenderung Komponentendoku nicht als neues grosses Doc, sondern in `architecture.yml` unter `assets`/`owns` erweitern.

## Asset Loading

Staerken:

- `hp_asset_version()` zentralisiert Cache Busting.
- `hp_enqueue_deferred_script()` nutzt native Script-Strategie.
- Graph, Votes, Single, Kontakt, Front Page und Legal werden bedingt geladen.

Risiken:

- Newsletter-CSS laedt auf fast allen Seiten ausser Kontakt. Das ist funktional plausibel, aber sollte nur so bleiben, wenn Header-Modal global wirklich sichtbar ist.
- Link-Preview-JS laedt auf allen relevanten Singulars, aber nicht auf Pages, obwohl alte Docs Pages nennen. Code und Docs muessen synchron bleiben.
- Graph-Asset-Versionierung nutzt in `graph-api.php` noch eigene `filemtime()`-Logik statt `hp_asset_version()`.

Hoher ROI:

- Graph-Asset-Versionierung auf `hp_asset_version()` umstellen.
- Asset-Matrix bei jeder Enqueue-Aenderung als CI-Drift-Check pruefen.

## Dead CSS / Removed Legacy Assets

Staerken:

- Diaspora-Assets sind als archived/deactivated dokumentiert.
- Generated D3-Bundle ist klar markiert.

Risiken:

- Entfernte Legacy-Dateien duerfen nicht wieder als aktive Orientierung in Agenten-Dokumente rutschen.
- `assets/js/glossar-tooltip.js` ist nicht aktiv, bleibt aber im Repo.

Hoher ROI:

- In `docs/AI_CONTEXT.md` und `docs/architecture.yml` archived Assets weiterhin explizit ausschliessen.
- Keine Loeschung ohne Produktentscheidung; der ROI liegt in Kontext-Hygiene, nicht in Dateientfernung.

## Conditional Loading

Hoher ROI:

- Bevor weitere CSS-Dateien extrahiert werden, pro Datei einen Owner und Load Condition in `docs/architecture.yml` eintragen.
- Neue Features duerfen nicht in `style.css`, wenn sie nur auf einer Page/CPT erscheinen.
- Fuer CSS-Split kein Build-System einfuehren, solange HTTP/2 Requests und Theme-Komplexitaet keinen messbaren Schmerz erzeugen.

## Prioritaeten

| Prio | Massnahme | ROI |
|---|---|---|
| P1 | Feature-/Page-CSS nach vorhandener `CSS_INVENTORY.md` weiter extrahieren | Hoch |
| P1 | `style.css`-Ownership in `docs/architecture.yml` und `change-impact.yml` sichtbar machen | Hoch |
| P1 | Graph-Asset-Versionierung vereinheitlichen | Mittel |
| P2 | Inline-Editor-JS in Assets verschieben | Hoch |
| P2 | Optional Design-Token-Inventar erzeugen | Mittel |
| P3 | Geloeschte Diaspora-Assets nur ueber Git-History wiederherstellen | Niedrig |
