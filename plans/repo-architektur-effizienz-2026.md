# Repo-Architektur effizienter, tokeneffizienter und intelligenter

> Erstellt: 2026-05-24  
> Basis: `main` nach Fast-forward auf `origin/main` (`483a2f9`)  
> Scope: WordPress-Child-Theme, Modulstruktur, Laufzeit-Performance, KI-/Agent-Kontextkosten

---

## Kurzdiagnose

Das Theme ist bereits in sinnvolle PHP-Module unter `inc/` getrennt und laedt Assets weitgehend bedingt. Die groessten Effizienzprobleme liegen nicht mehr im einfachen "alles global laden", sondern in drei Bereichen:

| Bereich | Befund | Effekt |
|---|---:|---|
| CSS | `style.css` hat 7.139 Zeilen | teuer fuer Reviews, KI-Kontext und gezielte Aenderungen |
| Newsletter | `inc/newsletter.php` hat 1.816 Zeilen | zu viele Verantwortlichkeiten in einer Datei |
| Kontakt | `inc/contact.php` hat 930 Zeilen | Formular, Seite, Mailer und Provider-Fallback vermischt |
| Wissensgraph | Graph-Build kombiniert Vollscan, O(n^2)-Topic-Vergleich und Regex-Suche | wird mit wachsendem Content teurer |
| KI-Kontext | `.github/copilot-instructions.md` nennt entfernte/veraenderte Dateien und alte Annahmen | Agenten lesen falschen Kontext und verbrauchen mehr Tokens |
| Artefakte | Fonts in vielen Formaten, deaktivierte Diaspora-Assets, lokale `_stitch/`-Artefakte | Repo ist groesser und unklarer als der aktive Ladepfad |

Zielbild: klare Domaenen, kleine Einstiegspunkte, maschinenlesbare Architekturkarte, bedingte Assets, messbare Budgets und weniger Kontextballast fuer Mensch und KI.

---

## Zielarchitektur

```text
functions.php
  -> inc/bootstrap.php
       -> inc/manifest.php          # explizite Modul-Ladereihenfolge
       -> inc/core/*                # Helpers, Theme-Support, Enqueue, GP-Compat
       -> inc/content/*             # CPTs, Taxonomien, Glossar, Dossier
       -> inc/graph/*               # Graph-Daten, Cache, REST, Mini-Graph
       -> inc/seo/*                 # Meta, Schema, Hygiene, Breadcrumbs
       -> inc/forms/contact/*       # Kontakt-Konfig, Validation, Mail, Handler, Admin
       -> inc/forms/newsletter/*    # Newsletter-Konfig, Storage, Mail, Handler, Admin
       -> inc/engagement/*          # Kommentare, Votes

assets/css/
  base.css
  layout.css
  components/
  pages/
  features/

docs/
  ARCHITECTURE.md
  AI_CONTEXT.md
  HOOKS.md
  ASSET_MATRIX.md
```

`functions.php` bleibt Bootstrap-only. Die Migration passiert stufenweise mit Wrappern, damit existierende Funktionsnamen (`hp_*`) und Hooks stabil bleiben.

---

## Phase 1 - Token- und Kontexteffizienz

**Ziel:** Agenten und Menschen sollen die Repo-Struktur in 1-2 kleinen Dateien verstehen, ohne `style.css`, `newsletter.php` oder grosse Templates vollstaendig laden zu muessen.

Aktionen:

1. `.github/copilot-instructions.md` aktualisieren:
   - `assets/js/journal.js` entfernen, weil der Code inzwischen in `nav.js`, `journal-single.js`, `link-preview.js` und Spezialskripte verteilt ist.
   - Font-Regel korrigieren: Aktuell liegen noch `eot`, `svg`, `ttf`, `woff`, `woff2` im Repo; Zielregel separat als Cleanup markieren.
   - Neue Module wie `seo-hygiene.php`, `glossary.php`, `dossier.php`, `graph-api.php`, `mini-graph.php`, `newsletter.php`, `contact.php`, `votes.php` aufnehmen.

2. `docs/AI_CONTEXT.md` anlegen:
   - 80-120 Zeilen Maximalumfang.
   - Zweck, aktive Features, wichtigste Dateien, "nicht zuerst lesen"-Liste.
   - Kontextbudget pro Aufgabe, z. B. "Kontaktformular: nur `inc/contact.php`, `inc/contacts-admin.php`, `page-kontakt.php`".

3. `docs/ARCHITECTURE.md` anlegen:
   - Modulkarte mit Verantwortlichkeit, Hooks, REST-Routen und Datenquellen.
   - Keine langen Codebeispiele, nur Landkarte.

4. `docs/ASSET_MATRIX.md` anlegen:
   - Asset, Handle, geladen auf, Abhaengigkeit, Groesse, Besitzer-Modul.
   - Beispiel: `hp-link-preview` nur auf Singles/Pages, REST `hp/v1/link-preview`.

5. Optionales Script `scripts/context-map.sh`:
   - gibt `git status`, Modul-Liste, groesste Dateien, REST-Routen und Asset-Matrix kompakt aus.
   - kein generierter Volltext im Prompt, sondern ein reproduzierbarer Kurzreport.

Definition of Done:

- Neue Agenten muessen fuer Architekturfragen nicht mehr zuerst alle grossen Dateien lesen.
- Veraltete Copilot-Hinweise sind entfernt.
- Grosse generierte/minifizierte/lokale Artefakte sind in der Doku als "nur bei Bedarf lesen" markiert.

---

## Phase 2 - Modulstruktur nach Domaenen

**Ziel:** Die flache `inc/`-Struktur in Domaenen aufteilen, ohne WordPress-Hooks oder bestehende `hp_*`-APIs zu brechen.

Aktionen:

1. `inc/bootstrap.php` einfuehren:
   - laedt `inc/manifest.php`;
   - prueft Dateien;
   - laedt Module in definierter Reihenfolge.

2. `inc/manifest.php` als zentrale Modulkarte:

```php
return [
    'core/helpers.php',
    'core/enqueue.php',
    'content/post-types.php',
    'content/taxonomies.php',
    'seo/meta.php',
    'seo/schema.php',
    'forms/contact/index.php',
    'forms/newsletter/index.php',
];
```

3. Migration ohne Big-Bang:
   - Erst Dateien verschieben und `require_once` anpassen.
   - Danach grosse Module intern splitten.
   - Alte Dateinamen bei Bedarf fuer eine Version als Shim behalten.

4. Hook-Registrierung sichtbar machen:
   - Am Ende jedes Moduls bleibt der Hook-Block.
   - In `docs/HOOKS.md` werden Hook, Callback, Prioritaet und Modul dokumentiert.

Definition of Done:

- `functions.php` hat nur noch `require_once inc/bootstrap.php`.
- Jedes Modul liegt in einer fachlichen Domaene.
- Die Ladereihenfolge ist in einer Manifest-Datei erkennbar statt als lange Liste in `functions.php`.

---

## Phase 3 - Grosse Dateien splitten

**Ziel:** Kleine, gezielte Dateien senken Review-Kosten, Merge-Konflikte und Tokenverbrauch.

Prioritaet 1: Newsletter

```text
inc/forms/newsletter/
  index.php
  config.php
  storage.php
  tokens.php
  mail-templates.php
  mailer.php
  handlers.php
  render.php
  admin.php
  cleanup.php
```

Regel: `index.php` registriert nur Includes. Bestehende Funktionsnamen bleiben zunaechst erhalten, damit keine Templates brechen.

Prioritaet 2: Kontakt

```text
inc/forms/contact/
  index.php
  config.php
  page.php
  validation.php
  mailer-brevo.php
  mailer-wp.php
  handlers.php
  render.php
  admin-storage.php
```

Prioritaet 3: CSS

```text
assets/css/
  base.css
  typography.css
  header.css
  content.css
  forms.css
  graph.css
  components/
  pages/
```

`style.css` bleibt wegen WordPress-Theme-Metadaten erhalten. Danach gibt es zwei Optionen:

| Option | Vorteil | Nachteil |
|---|---|---|
| Mehrere bedingte CSS-Dateien enqueuen | kein Build-Step, klarer Ladepfad | mehr Requests, aber HTTP/2 meist ok |
| `style.css` als Build-Artefakt aus Partials | beste Laufzeit und sauberer Source | Build-Prozess muss gepflegt werden |

Empfehlung: Erst ohne Build-Step in bedingte CSS-Dateien splitten. Falls die Requests messbar stoeren, spaeter buendeln.

Definition of Done:

- Keine aktive PHP-Datei ueber 700 Zeilen, ausser bewusst generierte/Seeder-Dateien.
- `style.css` sinkt auf Theme-Metadaten, Tokens und wirklich globale Basisregeln.
- Feature-CSS wird nur dort geladen, wo das Feature sichtbar ist.

---

## Phase 4 - Laufzeit effizienter machen

**Ziel:** Content-Wachstum soll Graph, Link-Preview, Glossar und Caches nicht ueberproportional verteuern.

Aktionen:

1. Graph-Build umbauen:
   - Shared-Topic-Edges ueber `topic_id -> post_ids` erzeugen statt alle Post-Paare mit `array_intersect()` zu vergleichen.
   - Glossar-Suche ueber einen zentralen Term-Index vorbereiten: normalisierte Begriffe, Synonyme, Ziel-ID.
   - Knotenlimit und Edge-Limit getrennt konfigurieren.

2. Cache-Versionen trennen:
   - `hp_glossar_version` nicht mehr als globale Version fuer Graph und Link-Preview missbrauchen.
   - Neue Optionen: `hp_graph_version`, `hp_link_preview_version`, `hp_glossar_autolink_version`.

3. Direkte SQL-Transient-Loeschung reduzieren:
   - Primaer versionierte Cache-Keys nutzen.
   - Alte Transients per geplantem Cleanup entfernen statt bei jedem Save `LIKE`-Deletes auszufuehren.

4. Link-Preview feinere Invalidierung:
   - Cache-Key auf `post_id + post_modified_gmt + preview_schema_version`.
   - Glossar-Aenderungen invalidieren nur Glossar-bezogene Preview-Daten, nicht alles.

5. Editor-Inline-JS auslagern:
   - Gutenberg-Panels aus `wp_add_inline_script()` in kleine Editor-Assets verschieben.
   - Vorteil: bessere Lesbarkeit, Caching, weniger PHP-String-Code.

Definition of Done:

- Graph-Build skaliert naeher an O(posts + topic_edges + term_hits) statt O(posts^2 + posts*terms).
- Cache-Invalidierung ist fachlich getrennt.
- Save-Operationen machen keine unnoetigen breiten Datenbank-Deletes.

---

## Phase 5 - Intelligenteres Repo

**Ziel:** Das Repo soll seine eigene Architektur, Datenfluesse und Qualitaetsgrenzen maschinenlesbar machen.

Aktionen:

1. `architecture.json` oder `docs/architecture.yml`:
   - Module, Hooks, REST-Routen, Assets, Templates, Datenbanktabellen.
   - Kann manuell starten und spaeter aus Code generieren.

2. Feature-Readmes:
   - `inc/forms/newsletter/README.md`
   - `inc/forms/contact/README.md`
   - `inc/graph/README.md`
   - Inhalt: Zweck, Datenmodell, Hooks, bekannte Grenzen, Testfaelle.

3. Test- und Check-Kommandos standardisieren:
   - `composer lint:php` oder `scripts/lint-php.sh`
   - `npm --prefix _build-d3 run build` fuer D3
   - `scripts/smoke-assets.sh` fuer fehlende Asset-Dateien/Handles

4. CI einfuehren:
   - PHP-Syntaxcheck fuer alle Theme-PHP-Dateien.
   - Optional PHPCS mit WordPress-Standards.
   - Optional PHPStan/Psalm mit WordPress-Stubs fuer reine Helper und Storage-Funktionen.

5. Budgets definieren:
   - Max. globale JS-KB.
   - Max. aktive CSS-KB pro Seitentyp.
   - Max. Zeilen pro Feature-Datei.
   - Max. Prompt-Kontext fuer Standardaufgaben.

Definition of Done:

- Jede neue Funktion hat eine erkennbare Domaene.
- Jede REST-Route und jedes Frontend-Asset ist in einer Matrix auffindbar.
- CI faengt Syntax- und offensichtliche Strukturfehler ab, bevor sie in `main` landen.

---

## Phase 6 - Repo-Gewicht und Altlasten

**Ziel:** Aktiver Code, Archiv-Code und lokale Artefakte klar trennen.

Aktionen:

1. Fonts bereinigen:
   - Ziel: Nur `woff2` und optional `woff`.
   - `eot`, `svg`, `ttf` nur behalten, wenn es eine echte Browser-Anforderung gibt.
   - Danach `.github/copilot-instructions.md` und CSS-Kommentare angleichen.

2. Diaspora-Archiv markieren:
   - Wenn deaktiviert: `archive/diaspora/` oder `experiments/diaspora/`.
   - Nicht im aktiven Asset-Pfad.
   - In `docs/AI_CONTEXT.md` als "nicht fuer normale Theme-Aenderungen lesen" markieren.

3. `_stitch/` entscheiden:
   - Wenn lokales Tool: komplett ignorieren, inklusive `package-lock.json`.
   - Wenn Teil des Produkts: `package.json`, README und klare Domaene ergaenzen.
   - Aktuell ist `_stitch/package-lock.json` unversioniert sichtbar, `.env` und `node_modules` sind ignoriert.

4. Build-Artefakte kennzeichnen:
   - `assets/js/d3-custom.min.js` als generiert dokumentieren.
   - Source bleibt `_build-d3/src/d3-custom.js`.

Definition of Done:

- `git status` zeigt nach normalem Arbeiten keine lokalen Tool-Artefakte.
- Agenten lesen keine minifizierten/generated Dateien fuer Architekturfragen.
- Archivierte Experimente koennen nicht versehentlich in den aktiven Ladepfad rutschen.

---

## Reihenfolge der Umsetzung

| Schritt | Aufwand | Risiko | Wirkung |
|---|---:|---:|---:|
| Copilot-/AI-Kontext aktualisieren | klein | niedrig | hoch fuer Tokenkosten |
| `docs/ARCHITECTURE.md` + `ASSET_MATRIX.md` | klein | niedrig | hoch fuer Orientierung |
| `_stitch/`-Entscheidung + `.gitignore` finalisieren | klein | niedrig | mittel |
| Newsletter splitten | mittel | mittel | hoch |
| Kontakt splitten | mittel | mittel | hoch |
| CSS bedingt splitten | mittel | mittel | hoch |
| Graph-Builder optimieren | mittel | mittel | hoch bei Content-Wachstum |
| CI/PHPCS/PHPStan einfuehren | mittel | niedrig-mittel | mittel-hoch |

Empfohlener erster Sprint:

1. Doku-/Kontext-Sprint: Copilot, `AI_CONTEXT.md`, `ARCHITECTURE.md`, `ASSET_MATRIX.md`.
2. Hygiene-Sprint: `_stitch/`, Fonts, Diaspora-Kennzeichnung.
3. Struktur-Sprint: `inc/bootstrap.php` + Manifest ohne fachliche Logik-Aenderung.
4. Split-Sprint: Newsletter und Kontakt in kleinere Domaenen-Dateien.
5. Performance-Sprint: Graph-Build und Cache-Versionen.

---

## Sofortige naechste Tasks

1. `.github/copilot-instructions.md` auf aktuellen Stand bringen.
2. `docs/AI_CONTEXT.md` mit einer kompakten "Was zuerst lesen?"-Matrix anlegen.
3. `_stitch/package-lock.json` entweder ignorieren oder `_stitch/` als echtes Teilprojekt mit `package.json` dokumentieren.
4. `inc/newsletter.php` anhand der Funktionsgruppen in 8-10 Dateien aufteilen, ohne Public-Funktionsnamen zu aendern.
5. `style.css` nach globalen Basisregeln, Komponenten und Seitentypen inventarisieren.
