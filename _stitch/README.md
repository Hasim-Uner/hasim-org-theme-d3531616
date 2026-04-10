# Google Stitch Design Studio — Hasim Integration

AI-gestützte UI-Design-Generierung für hasimuener.org via [Google Stitch](https://stitch.withgoogle.com).

## Was ist das?

Google Stitch ist ein AI-Tool von Google Labs, das aus Text-Prompts komplette UI-Designs (HTML/CSS + Screenshots) generiert. Diese Integration ermöglicht es, direkt aus dem Theme heraus Designs zu erstellen, zu exportieren und im WordPress-Admin zu verwalten.

## Setup

### 1. API-Key besorgen

1. Gehe zu [stitch.withgoogle.com](https://stitch.withgoogle.com)
2. Profilbild → **Stitch Settings** → **API Keys** → **Create Key**
3. Den Key sicher notieren (wird nur einmal angezeigt)

### 2. Environment konfigurieren

```bash
cd _stitch
cp .env.example .env
# .env editieren und STITCH_API_KEY eintragen
```

**Wichtig:** Die `.env` Datei ist gitignored und wird niemals committet.

### 3. Dependencies installieren

```bash
npm install
```

## Verwendung

### Design generieren

```bash
# Einfaches Design
npm run generate -- --prompt "Editorial-Startseite für politisches Magazin"

# Mit Export (HTML + Screenshot)
npm run generate -- --prompt "Artikelseite mit Lesefortschritt" --export

# Mobile Variante
npm run generate -- --prompt "Newsletter-Anmeldung" --device MOBILE --export

# Mit 3 Varianten
npm run generate -- --prompt "Dashboard für Autoren" --variants 3 --export
```

**Geräte-Typen:** `MOBILE` | `DESKTOP` | `TABLET` | `AGNOSTIC`

### Projekte und Screens auflisten

```bash
# Alle Projekte
npm run list

# Screens eines Projekts
npm run list -- --project PROJEKT_ID
```

### Bestehendes Design exportieren

```bash
npm run export -- --project PROJEKT_ID --screen SCREEN_ID
```

### Hasim Design-System erstellen/anwenden

Erstellt ein Design-System in Stitch, das die Hasim-Theme-Farben und Typografie (Merriweather, #b12a2a) verwendet:

```bash
# Design-System erstellen
npm run design-system -- --project PROJEKT_ID

# Design-System auf Screen anwenden
npm run design-system -- --project PROJEKT_ID --apply SCREEN_ID
```

## WordPress Admin

Generierte Designs werden automatisch im WordPress-Admin unter **Werkzeuge → Stitch Designs** angezeigt (mit Vorschaubildern und HTML-Links).

Siehe `inc/stitch-admin.php`.

## Verzeichnis-Struktur

```
_stitch/
├── .env                  # API-Key (GITIGNORED)
├── .env.example          # Template
├── package.json          # Node.js Dependencies
├── scripts/
│   ├── config.js         # Gemeinsame SDK-Konfiguration
│   ├── generate.js       # Design-Generierung
│   ├── list.js           # Projekte/Screens auflisten
│   ├── export.js         # Screen exportieren
│   └── design-system.js  # Hasim Design-System
└── output/               # Generierte HTML/PNG Dateien (GITIGNORED)
```

## Sicherheitshinweise

- **API-Key niemals committen.** Die `.env` ist in `.gitignore`.
- **Output-Dateien werden nicht committed**, da sie groß sein können und jederzeit neu generiert werden.
- Der API-Key wird über die Umgebungsvariable `STITCH_API_KEY` an das SDK übergeben.

## Technische Details

- **SDK:** [@google/stitch-sdk](https://github.com/google-labs-code/stitch-sdk)
- **Protokoll:** Google Stitch nutzt MCP (Model Context Protocol)
- **Endpoint:** `https://stitch.googleapis.com/mcp`
- **Auth:** `X-Goog-Api-Key` Header (vom SDK automatisch gesetzt)
- **Modelle:** Gemini 3 Pro / Gemini 3 Flash

## Troubleshooting

**Fehler: STITCH_API_KEY nicht gesetzt**
→ `.env` Datei im `_stitch/` Verzeichnis erstellen und Key eintragen.

**Fehler: 401 Unauthorized**
→ API-Key überprüfen. Unter [stitch.withgoogle.com](https://stitch.withgoogle.com) → Settings → API Keys einen neuen erstellen.

**Fehler: Network timeout**
→ Stitch-Server oder Firewall prüfen. Der Endpoint ist `stitch.googleapis.com`.
