# Editorial Knowledge Platform — WordPress Theme

Eigenständige WordPress-Codebase für eine redaktionelle Wissensplattform mit Essays, Dossiers, Glossar, thematischen Beziehungen und eigener Navigations- und Lesearchitektur.

Das Projekt zeigt vor allem **Custom WordPress Development jenseits klassischer Pagebuilder-Seiten**: eigene Templates, Content-Strukturen, Design-Tokens, technische Qualitätssicherung und eine bewusst entwickelte Informationsarchitektur.

## Technischer Fokus

- GeneratePress Child Theme
- eigene PHP-Templates für verschiedene Content-Typen
- Custom Post Types und Taxonomien
- Glossar- und Dossier-Strukturen
- Wissensgraph-/Relationship-Logik
- eigene Navigation und Suchoberfläche
- responsive Editorial UI
- Accessibility-Bausteine
- PHPStan-basierte Qualitätssicherung

## Architektur

```text
WordPress
   │
   ├── Essays
   ├── Notes
   ├── Dossiers
   ├── Glossary
   └── Topics
        │
        ↓
  Relationship Layer
        │
        ↓
 Navigation / Search / Knowledge Graph
```

## Frontend-System

Das Theme arbeitet mit eigenen Design-Tokens für Typografie, Abstände, Oberflächen, Akzentfarben und Content-Dichte. Ziel ist ein konsistentes redaktionelles System statt einzelner, voneinander unabhängiger Seitenlayouts.

Beispiele:

- lokal eingebundene Schriftdateien
- zentrale CSS Custom Properties
- responsive Typografie
- sticky Navigation
- Glossar-Index
- Dossier- und Archiv-Templates
- Zitier- und Lesekomponenten
- Reduced-Motion- und Focus-Visible-Unterstützung

## Engineering-Prinzipien

- Theme-Logik und Darstellung klar strukturiert
- wiederverwendbare Template-Parts
- möglichst wenig visuelle Logik im WordPress-Backend
- Änderungen über Git nachvollziehbar
- statische Analyse mit PHPStan
- Accessibility und Responsive-Verhalten als Teil der Implementierung

## Warum dieses Repository öffentlich ist

Das Repository dient als technischer Nachweis dafür, wie ich komplexere WordPress-Systeme strukturiere: nicht nur Seiten bauen, sondern Informationsarchitektur, Content-Modell und Frontend als zusammenhängendes System entwickeln.

Mehr zu meiner Arbeit: [hasimuener.de](https://hasimuener.de)
