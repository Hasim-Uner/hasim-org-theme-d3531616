# Agent Readiness Audit

Stand: 2026-06-02

## Gesamtscore

**82 / 100**

Das Repository ist fuer Cursor, Claude Code und OpenAI Agents bereits gut lesbar. Die groessten Pluspunkte sind Bootstrap-Manifest, vorhandene Architekturdocs, AI-Kontextkarte und bedingte Asset-Dokumentation. Die fehlenden Punkte lagen vor allem in maschinenlesbarer Ownership, Change-Impact und automatisch generierbaren Hook-/REST-Uebersichten.

## Bewertung

| Bereich | Score | Begruendung |
|---|---:|---|
| Einstieg & Orientierung | 90 | `functions.php`, `inc/bootstrap.php`, `inc/manifest.php`, `docs/AI_CONTEXT.md` und `docs/ARCHITECTURE.md` geben einen klaren Startpfad. |
| Modulgrenzen | 82 | Fachliche Module sind gut erkennbar. Einige grosse Dateien und Inline-JS verwischen Grenzen. |
| Ownership | 68 | Vor diesem Audit gab es keine dedizierte Agenten-/Owner-Datei. Ownership war ableitbar, aber nicht verbindlich. |
| Hidden Dependencies | 72 | Manifest macht Include-Reihenfolge sichtbar. Versteckte Kopplungen bleiben bei `hp_glossar_version`, Dossier-ID-Listen, Graph-Textscan und SEO-Cockpit-Caches. |
| Side Effects | 70 | Hooks sind verteilt und WordPress-typisch. Ohne generierte Hook-Doku muessen Agenten Side Effects per Suche rekonstruieren. |
| SEO-Verstaendlichkeit | 86 | SEO-Dateien sind gut kommentiert und fachlich stark. Entity-ID-Policy und KG-Mapping fehlten als zentrale Quelle. |
| Knowledge-Graph-Verstaendlichkeit | 78 | Graph, Glossar, Topics und Dossiers sind konzeptionell stark, aber Dossiers fehlen im REST-Graph und Beziehungen sind teils implizit. |
| Frontend-Verstaendlichkeit | 80 | Asset-Matrix und CSS-Inventar helfen stark. `style.css` bleibt ein hoher Kontextblock. |
| Automatisierbarkeit | 80 | CI existiert mit PHP-Lint, Manifest-Validierung, PHPStan und Drift-Check fuer generierte Hook-/REST-Dokumente. |
| Security Hygiene fuer Agenten | 74 | `.gitignore` schuetzt `inc/contact-local.php`; trotzdem existiert die Datei lokal und muss als No-Read/No-Quote-Zone dokumentiert bleiben. |

## Cursor

**Readiness: 84 / 100**

Cursor profitiert besonders von `docs/AI_CONTEXT.md` und den kleinen Bootstrap-Dateien. Risiko: Cursor liest bei breiten Kontextoperationen leicht `style.css`, grosse SEO-Cockpit-Dateien oder lokale Credential-Dateien. Das neue `docs/AGENTS.md` und `docs/change-impact.yml` reduziert dieses Risiko.

## Claude Code

**Readiness: 81 / 100**

Claude Code kann das Repo gut ueber `rg` und Manifest erschliessen. Hauptproblem ist nicht Orientierung, sondern Seiteneffekt-Erkennung in Hooks und Meta-/Cache-Kopplungen. Generierte `docs/HOOKS.md` und `docs/REST_ROUTES.md` helfen hier direkt.

## OpenAI Agents

**Readiness: 82 / 100**

OpenAI Agents koennen das Repo nach Architekturdateien und Ownership-Modell gut in Teilaufgaben zerlegen. Fuer Multi-Agenten-Arbeit ist wichtig, dass SEO, Knowledge Graph, Content, Frontend, Performance und Analytics klare Verbote haben. Genau das leistet `docs/AGENTS.md`.

## Ownership-Probleme

- `style.css` gehoert praktisch allen Frontend-/Content-Systemen zugleich.
- `inc/seo-schema.php` nutzt Dossier-/Glossar-Funktionen und sollte nicht isoliert geaendert werden.
- `inc/graph-api.php` besitzt Graph-Daten, aber Glossar und Topics liefern semantische Quellen.
- SEO-Cockpit nutzt `nexus_*` statt `hp_*`; das ist als Subsystem okay, muss aber fuer Agenten explizit dokumentiert bleiben.
- Contact/Newsletter/Privacy teilen personenbezogene Datenfluesse.

## Versteckte Abhaengigkeiten

- `inc/link-preview.php` nutzt `hp_glossar_version` fuer Preview-Caches.
- `inc/mini-graph.php` liest das kompilierte Graph-Payload, darf keinen synchronen Rebuild erzwingen.
- `inc/seo-schema.php` erwartet `hp_get_seo_image_data()`, `hp_dossier_get_leseplan()`, `hp_dossier_get_begriffe()` und `hp_dossier_get_citations()`.
- `single-essay.php` ruft Votes, Mini-Graph, Newsletter, Dossier-Reverse-Lookup und Zentralbegriffe auf.
- Dossier-Beziehungen liegen als comma-separated Post-IDs in Postmeta.
- Glossar-Verwandtschaft liegt ebenfalls als comma-separated IDs in Postmeta.

## Undokumentierte Seiteneffekte

- Glossar-Cache-Migrationen loeschen Transients per SQL auf `init`.
- Votes-Modul prueft Tabellenexistenz auf jedem `init`.
- Dossier-Modul kann einmalig Rewrite Rules flushen.
- Privacy-Maintenance plant Cron auf `init`.
- SEO-Cockpit plant Cron auf `init` und nutzt Locks/Transients.
- Last-Modified/ETag kann Requests mit 304 frueh beenden.

## Fehlende Architekturinformationen

Durch diese Audit-Implementierung neu geschlossen:

- Maschinenlesbare Architekturkarte: `docs/architecture.yml`.
- Agenten-Ownership: `docs/AGENTS.md`.
- Change-Impact-Map: `docs/change-impact.yml`.
- Hook-Referenz: `docs/HOOKS.md`.
- REST-Referenz: `docs/REST_ROUTES.md`.
- Generator: `scripts/generate-wp-docs.php`.

Weiter offen:

- Vollstaendige maschinenlesbare Postmeta-Registry.
- Vollstaendige Datenbanktabellen-Registry.
- Entity-ID-Konvention als testbare Regel.
