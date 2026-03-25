# Audit: Diaspora-Architektur Seite
> Datum: 2026-03-25
> Geprüft von: Claude Opus 4.6
> Quelle: `page-diaspora-architektur.php`, `assets/css/diaspora-scroll.css`, `assets/js/diaspora-scroll.js`
> Hinweis: Performance-Budgets wurden in dieser Umgebung statisch geprüft; Lighthouse-Metriken wie LCP/CLS/INP wurden nicht live gemessen.

## Zusammenfassung
- 0 kritische Funde
- 2 hohe Funde
- 5 mittlere Funde
- 4 niedrige Funde

## Kritische Funde
Keine kritischen Funde im Sinne eines kompletten Funktionsausfalls. Die gravierendsten Probleme liegen in Dialog-A11y und mobiler Abschnittsnavigation.

## Hohe Funde
### [H-001] Detail-Overlay unterbricht Tastatur- und Screen-Reader-Nutzung
- **Bereich**: Barrierefreiheit
- **Sektion**: Alle Detail-Trigger / Overlay
- **Problem**: Das Overlay öffnet als Dialog, aber es gibt kein Focus-Trapping, keinen Focus-Return zum Auslöser und keine programmatische Verbindung zwischen Titel, Text und Dialog. Trigger setzen zudem keinen Status wie `aria-expanded`. Damit kann der Fokus hinter dem Overlay verschwinden und die Dialogbeziehung bleibt für Assistive Technology unklar.
- **Stelle**: `#da-detail-overlay` in `page-diaspora-architektur.php`; Dialoglogik in `assets/js/diaspora-scroll.js` (`initDetailPanels()`); Trigger mit `[data-detail]` im Template
- **Fix**: Dialog mit `aria-labelledby` und `aria-describedby` verdrahten, auslösendes Element speichern, Fokus im Overlay halten, bei Schließen zum Auslöser zurücksetzen und Triggerstatus (`aria-expanded`, `aria-controls`) synchronisieren.
- **Aufwand**: Mittel

### [H-002] In-Page-Navigation ist auf Mobile faktisch nicht vorhanden
- **Bereich**: UX/UI | Barrierefreiheit
- **Sektion**: Sticky TOC / Seitennavigation
- **Problem**: Die Abschnittsnavigation wird unter `900px` vollständig ausgeblendet. Gleichzeitig sind die Desktop-Trefferflächen sehr klein. Damit fehlt auf Mobile eine nutzbare Sprungnavigation, obwohl die Seite als lange Scroll-Strecke aufgebaut ist.
- **Stelle**: `.da-toc`, `.da-toc__item`, `.da-toc__dot` und Mobile-Regel in `assets/css/diaspora-scroll.css`; Navigation in `page-diaspora-architektur.php`
- **Fix**: Mobile-Variante der TOC sichtbar machen, Trefferflächen auf mindestens `44x44px` bringen und sichtbare `:focus-visible`-Zustände ergänzen.
- **Aufwand**: Mittel

## Mittlere Funde
### [M-001] Heading-Hierarchie springt mehrfach von `h2` auf `h4`
- **Bereich**: Barrierefreiheit
- **Sektion**: Inspirationsquelle, Mediale Architektur
- **Problem**: Mehrere Unterelemente verwenden `h4`, obwohl innerhalb derselben Sektion kein vorgelagertes `h3` existiert. Das erschwert die Gliederung für Screen Reader und verletzt die intendierte Struktur der Seite.
- **Stelle**: `.da-inspiration-card h4`, `.da-stack-layer h4`, `.da-module-card h4` in `page-diaspora-architektur.php`
- **Fix**: Interne Kartenüberschriften auf `h3` umstellen oder als nicht-strukturelle Labels ohne Heading-Level auszeichnen.
- **Aufwand**: Gering

### [M-002] Kampagnen-Widget kommuniziert Zustände nicht ausreichend an Assistive Technology
- **Bereich**: Barrierefreiheit
- **Sektion**: Kampagne 01
- **Problem**: Die Phasenbuttons sind zwar echte Buttons, setzen aber keinen Auswahlstatus. Detail-, Timeline- und Insight-Bereich haben keine Rollen/Labels bzw. keine Live-Ansage für Inhaltswechsel.
- **Stelle**: `#da-k-nav`, `#da-k-detail`, `#da-k-timeline`, `#da-k-insight` in `page-diaspora-architektur.php`; `initKampagne()` in `assets/js/diaspora-scroll.js`
- **Fix**: `aria-pressed` oder `aria-selected` auf Phase-Buttons setzen, dynamische Bereiche mit sinnvollen Rollen/Labels versehen und Statuswechsel per `aria-live="polite"` ankündigen.
- **Aufwand**: Gering

### [M-003] Font-Setup ist inkonsistent und verweist auf nicht vorhandene Assets
- **Bereich**: UX/UI
- **Sektion**: Global / Typografie
- **Problem**: `diaspora-scroll.css` deklariert `Outfit` und `Figtree` via `/fonts/...`, im Repo liegen dort aber nur Merriweather-Dateien. Dadurch fallen Teile der Seite auf Fallback-Schriften zurück; die beabsichtigte Zweifont-Hierarchie wird nicht zuverlässig erreicht.
- **Stelle**: `@font-face` in `assets/css/diaspora-scroll.css`; vorhandene Font-Dateien im Verzeichnis `fonts/`
- **Fix**: Echte `Outfit`- und `Figtree`-Assets ins Theme aufnehmen und korrekt referenzieren oder die Seite konsequent auf vorhandene Theme-Tokens umstellen.
- **Aufwand**: Mittel

### [M-004] Performance-Budgets werden deutlich überschritten
- **Bereich**: UX/UI
- **Sektion**: Asset-Ladepfad
- **Problem**: Das CSS liegt bei ca. `66.5KB`, `diaspora-scroll.js` bei ca. `40.2KB` und `d3.min.js` bei ca. `279.7KB`. Das überschreitet die im Prompt genannten Zielbudgets deutlich, insbesondere durch die voll gebündelte D3-Datei.
- **Stelle**: Enqueue in `page-diaspora-architektur.php`; Dateigrößen von `assets/css/diaspora-scroll.css`, `assets/js/diaspora-scroll.js`, `assets/js/d3.min.js`
- **Fix**: D3-Graph auf ein deutlich kleineres Rendering herunterbrechen oder statisch lösen, CSS aufteilen bzw. redundante Stile entfernen und nur wirklich notwendige Interaktionslogik laden.
- **Aufwand**: Hoch

### [M-005] Klickbare Modulkarten enthalten verschachtelte Interaktion
- **Bereich**: Barrierefreiheit | UX/UI
- **Sektion**: Mediale Architektur
- **Problem**: Die Wissensgraph-Karte ist als `role="button"` mit `tabindex="0"` klickbar, enthält aber zusätzlich einen echten Link. Das erzeugt konkurrierende Aktivierungsziele und eine uneindeutige Tab-Reihenfolge.
- **Stelle**: `.da-module-card[data-detail="wissensgraph_med"]` und `.da-module-link` in `page-diaspora-architektur.php`; globale `[data-detail]`-Delegation in `assets/js/diaspora-scroll.js`
- **Fix**: Karte entweder rein informativ machen und nur den Link/Detail-Button interaktiv halten oder die gesamte Karte als Link/Button neu modellieren.
- **Aufwand**: Gering

## Niedrige Funde
### [L-001] Footer-Hinweis nutzt nicht die explizit geforderte Formulierung
- **Bereich**: Content
- **Sektion**: Footer
- **Problem**: Die Seite sagt im Footer nur „Das ist Schicht 1.“ statt den Schicht-Bezug explizit und wiedererkennbar als „Diese Seite läuft auf Schicht 1“ zu formulieren.
- **Stelle**: `.da-meta-note` in `page-diaspora-architektur.php`
- **Fix**: Formulierung an die etablierte Schichtsprache angleichen.
- **Aufwand**: Gering

### [L-002] Fremdsprachige Begriffe sind nicht mit `lang` ausgezeichnet
- **Bereich**: Barrierefreiheit | Content
- **Sektion**: Rat-Details / Inspirationsquelle
- **Problem**: Begriffe wie `Kurmancî`, `Soranî`, `Zazakî` oder `Jin, Jiyan, Azadî` erscheinen ohne Sprachmarkierung. Screen Reader erhalten dadurch keine Aussprachehilfe.
- **Stelle**: Detailtexte in `assets/js/diaspora-scroll.js`
- **Fix**: Fremdsprachige Begriffe im injizierten Markup mit `lang="ku"` oder passender Sprachmarkierung versehen.
- **Aufwand**: Gering

### [L-003] SVG-Beschriftung der Ratsgrafik wird auf kleinen Screens schwer lesbar
- **Bereich**: UX/UI | Barrierefreiheit
- **Sektion**: Architektur des Kurdischen Rats
- **Problem**: Die Beschriftungen im `900x335`-SVG sind mit festen `9px` bis `16px` definiert. Auf schmalen Geräten skaliert der gesamte Graph herunter; die Texte werden sehr klein.
- **Stelle**: Inline-SVG in `page-diaspora-architektur.php`
- **Fix**: Mobile Fallback mit gestapelten HTML-Karten oder separater, vereinfachter SVG-Version ergänzen.
- **Aufwand**: Mittel

### [L-004] Mehrere Trigger sind weiterhin nur ARIA-buttons auf Nicht-Button-Elementen
- **Bereich**: Barrierefreiheit
- **Sektion**: Mehrere Sektionen
- **Problem**: Zahlreiche interaktive Elemente sind als `div` oder `blockquote` mit `role="button"` und `tabindex="0"` gebaut. Das funktioniert technisch, bleibt aber fragiler als native Buttons.
- **Stelle**: z.B. `.da-inspiration-card`, `.da-quote-block`, `.da-freiheit__rose-check`, `.da-stack-layer`
- **Fix**: Langfristig auf echte `button`-Elemente oder klar getrennte Link/Button-Strukturen umstellen.
- **Aufwand**: Mittel

## Content-Widersprüche
### [W-001] Kommune wird als Grundzelle eingeführt, später aber nicht mehr institutionell aufgenommen
- **Stelle A**: Inspirationsquelle, Karte „Die Kommune als Grundzelle“
- **Stelle B**: Ratsarchitektur und Kampagne sprechen nur noch über Rat, Ortsgruppen, Fachbereiche und Schichten
- **Widerspruch**: Die Kommune wird als fundamentale Basiseinheit eingeführt, taucht in der späteren Architektur aber nicht mehr als ordnendes Prinzip auf.
- **Empfehlung**: Ortsgruppen und Fachstrukturen explizit als kommunale bzw. aus der Kommune abgeleitete Ebenen benennen.

### [W-002] Dach-Metapher ist stark, der Dienstleistungscharakter des Rats bleibt aber implizit
- **Stelle A**: „Föderative Dacharchitektur“ und die Dach-Metapher im Rat-SVG
- **Stelle B**: Kampagnenteil betont Vertretung, aber nicht explizit „Dienstleister und Sprachrohr“
- **Widerspruch**: Die Seite will den Rat nicht hierarchisch lesen lassen, die dominante Dach-Sprache legt diese Lesart aber näher als die föderative Service-Logik.
- **Empfehlung**: Im Rat-Intro oder in der Summary explizit ergänzen, dass der Rat Dienstleister und Sprachrohr der Community ist, nicht deren Herrschaftsinstanz.

### [W-003] Demokratische Gesellschaft wird einmal abstrakt als „Akteur“, später konkret als „Vertretung“ gerahmt
- **Stelle A**: Inspirationsquelle: „eigenständigen Akteur“
- **Stelle B**: Kampagne 01: „Vertretung der kurdischen Community“
- **Widerspruch**: Die Abstraktion ist in der Grundlagensektion legitim, bleibt aber ohne Brücke zur konkreten institutionellen Vertretung stehen.
- **Empfehlung**: Eine kurze Übergangsformulierung ergänzen, dass der Rat die konkrete Vertretungsform dieser gesellschaftlichen Akteurschaft ist.

## Priorisierte Fix-Liste
| # | Fund | Bereich | Aufwand | Priorität |
|---|------|---------|---------|-----------|
| 1 | H-001 Dialog-A11y des Detail-Overlays | Barrierefreiheit | Mittel | HOCH |
| 2 | H-002 Mobile Abschnittsnavigation und Touch-Targets | UX/UI, Barrierefreiheit | Mittel | HOCH |
| 3 | M-002 ARIA-Status im Kampagnen-Widget | Barrierefreiheit | Gering | MITTEL |
| 4 | M-001 Heading-Hierarchie in Karten und Layern | Barrierefreiheit | Gering | MITTEL |
| 5 | M-005 Verschachtelte Interaktion in der Wissensgraph-Karte | UX/UI, Barrierefreiheit | Gering | MITTEL |
| 6 | M-003 Fehlende Outfit-/Figtree-Assets | UX/UI | Mittel | MITTEL |
| 7 | M-004 Asset-Budget und D3-Größe | UX/UI, Performance | Hoch | MITTEL |
| 8 | W-001 Kommune in spätere Architektur zurückführen | Content | Gering | MITTEL |
| 9 | W-002 Dienstleistungscharakter des Rats explizit machen | Content | Gering | MITTEL |
| 10 | L-001 Footer-Formulierung zu Schicht 1 präzisieren | Content | Gering | NIEDRIG |
| 11 | L-002 `lang`-Attribute für kurdische Begriffe ergänzen | Barrierefreiheit | Gering | NIEDRIG |
| 12 | L-003 Mobile Fallback für Rats-SVG | UX/UI | Mittel | NIEDRIG |
| 13 | L-004 Native Buttons statt ARIA-Buttons | Barrierefreiheit | Mittel | NIEDRIG |
