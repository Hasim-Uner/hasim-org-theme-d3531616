# Asset Matrix

Frontend assets are owned by PHP modules and should be loaded only where needed. For planned CSS source ownership, see `docs/CSS_INVENTORY.md`.

## Active Assets

| Handle | File | Owner | Loaded on | Dependencies | Runtime data |
|---|---|---|---|---|---|
| `generatepress-style` | parent `style.css` | `inc/enqueue.php` | all frontend pages | none | none |
| `hp-journal-style` | `style.css` | `inc/enqueue.php` | all frontend pages | `generatepress-style`, `generate-style` | none |
| `hp-front-page` | `assets/css/pages/front-page.css` | `inc/enqueue.php` | front page | `hp-journal-style` | none |
| `hp-mission-page` | `assets/css/pages/mission.css` | `inc/enqueue.php` | Mission page template or slug `mission` | `hp-journal-style` | none |
| `hp-error-page` | `assets/css/pages/error.css` | `inc/enqueue.php` | 404 pages | `hp-journal-style` | none |
| `hp-search-page` | `assets/css/pages/search.css` | `inc/enqueue.php` | search results | `hp-journal-style` | none |
| `hp-topic-archive` | `assets/css/pages/topic-archive.css` | `inc/enqueue.php` | `topic` taxonomy archive | `hp-journal-style` | none |
| `hp-archives` | `assets/css/pages/archives.css` | `inc/enqueue.php` | `essay`, `note`, `glossar` archives and `topic` taxonomy archive | `hp-journal-style` | none |
| `hp-contact-page` | `assets/css/pages/contact.css` | `inc/enqueue.php` | Kontakt page template or slug `kontakt` | `hp-journal-style` | none |
| `hp-newsletter` | `assets/css/components/newsletter.css` | `inc/enqueue.php` | all frontend pages except Kontakt | `hp-journal-style` | none |
| `hp-legal-pages` | `assets/css/pages/legal.css` | `inc/enqueue.php` | Impressum/Datenschutz page template or slug | `hp-journal-style` | none |
| `hp-single-editorial` | `assets/css/pages/single-editorial.css` | `inc/enqueue.php` | singular `essay`, `note` | `hp-journal-style` | none |
| `hp-related` | `assets/css/components/related.css` | `inc/enqueue.php` | singular `essay`, `note` | `hp-journal-style` | none |
| `hp-post-nav` | `assets/css/components/post-nav.css` | `inc/enqueue.php` | singular `essay`, `note` | `hp-journal-style` | none |
| `hp-nav-js` | `assets/js/nav.js` | `inc/enqueue.php` | all frontend pages | none | none |
| `hp-journal-single` | `assets/js/journal-single.js` | `inc/enqueue.php` | singular `essay`, `note`, `post` | none | none |
| `hp-link-preview` | `assets/js/link-preview.js` | `inc/enqueue.php`, `inc/link-preview.php` | singular `essay`, `note`, `post`, `glossar`, `dossier` | none | `hpLinkPreview.restUrl` |
| `hp-d3` | `assets/js/d3-custom.min.js` | `inc/graph-api.php` | page slug `wissensgraph` | none | none |
| `hp-graph-js` | `assets/js/graph.js` | `inc/graph-api.php` | page slug `wissensgraph` | `hp-d3` | `hpGraph.restUrl` |
| `hp-graph` | `assets/css/pages/wissensgraph.css` | `inc/graph-api.php` | page slug `wissensgraph` | `hp-journal-style` | none |
| `hasim-org-votes` | `assets/js/votes.js` | `inc/votes-api.php` | singular `essay`, `note` | none | `hasimOrgVotes.ajax_url`, `hasimOrgVotes.nonce` |
| `hasim-org-votes` | `assets/css/votes.css` | `inc/votes-api.php` | singular `essay`, `note` | none | none |
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
| `assets/js/d3-custom.min.js` | generated | source is `_build-d3/src/d3-custom.js` |

## Asset Rules

- Keep global assets rare: currently `style.css` and `nav.js`.
- New feature JS/CSS should have a clear owner module and load condition.
- Use `filemtime()` for theme assets where cache busting is needed.
- Do not edit minified/generated bundles directly.
- Update this matrix when adding, renaming, removing, or changing load conditions for assets.
