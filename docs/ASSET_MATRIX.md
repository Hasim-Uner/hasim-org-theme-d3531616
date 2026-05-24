# Asset Matrix

Frontend assets are owned by PHP modules and should be loaded only where needed. For planned CSS source ownership, see `docs/CSS_INVENTORY.md`.

## Active Assets

| Handle | File | Owner | Loaded on | Dependencies | Runtime data |
|---|---|---|---|---|---|
| `generatepress-style` | parent `style.css` | `inc/enqueue.php` | all frontend pages | none | none |
| `hp-journal-style` | `style.css` | `inc/enqueue.php` | all frontend pages | `generatepress-style`, `generate-style` | none |
| `hp-legal-pages` | `assets/css/pages/legal.css` | `inc/enqueue.php` | Impressum/Datenschutz page template or slug | `hp-journal-style` | none |
| `hp-nav-js` | `assets/js/nav.js` | `inc/enqueue.php` | all frontend pages | none | none |
| `hp-journal-single` | `assets/js/journal-single.js` | `inc/enqueue.php` | singular `essay`, `note`, `post` | none | none |
| `hp-link-preview` | `assets/js/link-preview.js` | `inc/enqueue.php`, `inc/link-preview.php` | singular `essay`, `note`, `post`, `glossar`, `dossier`, `page` | none | `hpLinkPreview.restUrl` |
| `hp-d3` | `assets/js/d3-custom.min.js` | `inc/graph-api.php` | page slug `wissensgraph` | none | none |
| `hp-graph-js` | `assets/js/graph.js` | `inc/graph-api.php` | page slug `wissensgraph` | `hp-d3` | `hpGraph.data`, `hpGraph.restUrl` |
| `hasim-org-votes` | `assets/js/votes.js` | `inc/votes-api.php` | singular/archive `essay`, `note` | `jquery` | `hasimOrgVotes.ajax_url`, `hasimOrgVotes.nonce` |
| `hasim-org-votes` | `assets/css/votes.css` | `inc/votes-api.php` | singular/archive `essay`, `note` | none | none |
| `comment-reply` | WordPress core | `inc/comments.php` | singular comment targets when threaded comments need it | core | none |

## Editor/Admin Assets

| Handle | File/source | Owner | Loaded on |
|---|---|---|---|
| `hp-social-teaser-panel` | inline JS attached to `wp-edit-post` | `inc/meta-fields.php` | block editor for supported content |
| `hp-seo-meta-panel` | inline JS attached to `wp-edit-post` | `inc/seo-meta.php` | block editor for SEO-enabled content |
| `hp-seo-cockpit-admin` | `assets/css/seo-cockpit-admin.css` | `inc/seo-cockpit/seo-cockpit-core.php` | SEO Cockpit admin screens and WP dashboard |
| `wp-edit-post` inline panel | inline JS | `inc/glossary.php` | glossary editor |
| `wp-edit-post` inline panel | inline JS | `inc/dossier.php` | dossier editor |

## Inactive Or Archived Assets

| File | Status | Notes |
|---|---|---|
| `assets/js/glossar-tooltip.js` | not currently enqueued | link preview script now handles glossary chip tooltip behavior |
| `assets/css/diaspora-scroll.css` | deactivated | only for archived/password Diaspora page |
| `assets/js/diaspora-scroll.js` | deactivated | only for archived/password Diaspora page |
| `page-diaspora-architektur.php` enqueue block | commented out | references older D3 setup; update before reactivation |
| `assets/js/d3-custom.min.js` | generated | source is `_build-d3/src/d3-custom.js` |

## Asset Rules

- Keep global assets rare: currently `style.css` and `nav.js`.
- New feature JS/CSS should have a clear owner module and load condition.
- Use `filemtime()` for theme assets where cache busting is needed.
- Do not edit minified/generated bundles directly.
- Update this matrix when adding, renaming, removing, or changing load conditions for assets.
