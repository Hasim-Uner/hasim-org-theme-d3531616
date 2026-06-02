# SEO Champion Audit

Stand: 2026-06-02

Fokus: nur Massnahmen mit hohem SEO-Impact fuer dieses konkrete Theme.

## Befund

Das Theme hat bereits starke technische SEO-Grundlagen: eigene Meta-Ausgabe, Canonicals, hreflang, JSON-LD, Breadcrumbs, Sitemap-Hygiene, Attachment-/Author-Redirects, noindex fuer Low-Value-Zonen, Last-Modified/ETag und ein Search-Console-basiertes SEO-Cockpit.

Der naechste SEO-Hebel liegt nicht in generischen Checks, sondern in Entity SEO und Wissensmodellierung.

## High-Impact-Massnahmen

### 1. Stabile Entity-ID-Policy fuer alle Wissensknoten

Impact: sehr hoch

Aktuell existieren `#person`, `#organization`, `#website`, `#term` und seitenbezogene Article-Schemata. Es fehlt eine zentrale Policy:

- Person: `home_url('/') . '#person'`
- Organization: `home_url('/') . '#organization'`
- WebSite: `home_url('/') . '#website'`
- Essay: `get_permalink($post) . '#article'`
- Note: `get_permalink($post) . '#article'`
- Glossar: `get_permalink($post) . '#term'`
- Dossier: `get_permalink($post) . '#dossier'`
- Topic: `get_term_link($term) . '#topic'`

Nutzen: Google und andere KG-Systeme koennen Entitaeten stabil zusammenfuehren statt pro Template neue Blank Nodes zu sehen.

### 2. Dossiers in den expliziten Graph aufnehmen

Impact: sehr hoch

`graph-api.php` modelliert Essays, Notes, Glossar und Topics. Dossiers sind aber die staerksten kuratierten Wissensknoten und fehlen im Graph-Payload.

Ziel-Edges:

- `dossier_has_part`: Dossier -> Essay/Note aus `_hp_dossier_leseplan`
- `dossier_mentions_term`: Dossier -> Glossar aus `_hp_dossier_begriffe`
- `dossier_in_topic`: Dossier -> Topic
- `dossier_cites`: optional aus Quellen, wenn URLs extrahierbar sind

Nutzen: Dossiers werden nicht nur als Template, sondern als zentrale semantische Hub-Entitaeten sichtbar.

### 3. Glossar-Schema erweitern

Impact: hoch

`DefinedTerm` ist vorhanden. Erweitern:

- `alternateName` aus `_hp_glossar_synonyme`
- `inDefinedTermSet` mit stabiler `@id`
- `sameAs` oder `identifier`, falls externe IDs spaeter gepflegt werden
- `isPartOf` WebSite
- `relatedLink` oder `seeAlso` fuer `_hp_glossar_verwandt`
- Sprachvarianten als `translationOfWork`/`workTranslation` nur vorsichtig, alternativ als `alternateName` mit Sprachhinweis in eigenem Modell

Nutzen: Begriffsseiten werden zu klareren Entity-Knoten und verbessern Topic Authority.

### 4. Topic-Pillar-Seiten als `CollectionPage` + `about` modellieren

Impact: hoch

Archive erhalten `CollectionPage`. Topic-Pages sollten zusaetzlich eine Topic-Entity referenzieren:

- `about: { @id: term_url + '#topic', @type: Thing, name, description }`
- `mainEntity` als `ItemList`
- Cross-Links zu anderen Topics als `relatedLink`

Nutzen: Topic-Pages werden nicht nur Listen, sondern semantische Themen-Hubs.

### 5. Interne Linksignale aus Dossier/Glossar/Topic priorisieren

Impact: hoch

Das Theme erzeugt bereits viele Links. Der Hebel ist Gewichtung:

- Dossier-Leseplan-Links sind starke kuratierte Links.
- Begriffsapparat-Links sind Entity-Links.
- Topic-Pillar-Links sind Cluster-Links.
- Mini-Graph-Links sind Kontextlinks.

SEO-Cockpit sollte diese Linktypen getrennt ausweisen, damit ein Agent nicht nur Linkanzahl, sondern semantische Linkqualitaet optimiert.

### 6. Link-Preview- und Glossar-Autolink-Index vereinheitlichen

Impact: mittel bis hoch

Autolinking, zentrale Begriffe und Graph scannen aehnliche Glossar-Daten getrennt. Ein gemeinsamer Term-Index wuerde:

- False Positives reduzieren.
- Beziehungen konsistenter machen.
- Graph-Edges und sichtbare Links zusammenhalten.
- Cache-Invalidierung fachlich sauber machen.

### 7. Dossier-Zitationsdaten als Schema und sichtbarer Vertrauensanker

Impact: hoch

Dossier-Schema gibt `citation` aus. Naechster Schritt:

- `@id` fuer Dossier-Article setzen.
- `version`, `dateModified`, `citation`, `author`, `publisher` konsistent halten.
- Quellen nicht nur sichtbar, sondern optional als `citation`-Array ausgeben.

Nutzen: Dossiers werden zitierfaehige Knowledge Assets, nicht nur Landingpages.

### 8. Indexierungslogik fuer deaktivierte Dossiers zentralisieren

Impact: mittel

Deaktivierte Dossiers werden aus Queries entfernt und direkt auf 404 gesetzt. Diese Logik sollte in Sitemap, Graph, Schema und Linkanalyse explizit als Status erscheinen, damit keine internen Links auf deaktivierte Dossiers entstehen.

### 9. SEO-Cockpit-Insights maschinenlesbar machen

Impact: hoch

Das Cockpit erkennt Quick Wins, Query Movers, Problem Pages und interne Linkkontexte. Diese Insights sollten als JSON/Option exportierbar sein, damit Agenten daraus Aufgaben erzeugen koennen:

- URL
- Rolle
- Problemtyp
- Prioritaet
- empfohlene Datei/Systeme aus `change-impact.yml`

### 10. llms.txt mit Knowledge-Hubs synchronisieren

Impact: mittel bis hoch

`llms-txt.php` existiert. Es sollte sicherstellen, dass Mission, Dossiers, Glossar-Archiv, Wissensgraph und wichtigste Topic-Hubs priorisiert sind. Das ist kein klassisches Google-SEO, aber stark fuer AI Retrieval und Entity-Verstaendnis.
