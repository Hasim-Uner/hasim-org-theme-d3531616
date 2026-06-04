# AI Context Map

Purpose: keep AI and human review focused. Start with this file, then open only the files relevant to the task.

## Repo Shape

- WordPress child theme for GeneratePress.
- `functions.php` loads `inc/bootstrap.php`; `inc/bootstrap.php` loads modules from `inc/manifest.php`.
- Active CSS is still concentrated in `style.css`.
- Frontend JS is split by feature under `assets/js/`.
- Generated/minified code should not be read for normal theme work.

## Read By Task

| Task | Start here | Usually also read |
|---|---|---|
| Bootstrap/module order | `functions.php`, `inc/bootstrap.php`, `inc/manifest.php` | `docs/ARCHITECTURE.md` |
| Asset loading | `inc/enqueue.php` | `inc/graph-api.php`, `inc/votes-api.php`, `docs/ASSET_MATRIX.md` |
| Header/navigation | `inc/header-nav.php` | `assets/js/nav.js`, header sections in `style.css` |
| Essay/note templates | `single-essay.php`, `single-note.php` | `assets/js/journal-single.js`, `assets/css/pages/single-editorial.css`, `assets/css/components/related.css`, `assets/css/components/post-nav.css` |
| Topic archive | `taxonomy-topic.php` | `inc/taxonomies.php`, `assets/css/pages/topic-archive.css` |
| Glossary | `inc/glossary.php` | `single-glossar.php`, `archive-glossar.php`, `assets/js/link-preview.js`; use `hp_glossar_get_term_index()` for title/synonym matching |
| Dossier | `inc/dossier.php` | `single-dossier.php`, `archive-dossier.php` |
| Wissensgraph | `inc/graph-api.php` | `assets/js/graph.js`, `page-wissensgraph.php`, `_build-d3/src/d3-custom.js` |
| Mini graph | `inc/mini-graph.php` | `inc/graph-api.php`, CSS in `style.css` |
| SEO/meta/schema/sitemap | `inc/seo-meta.php`, `inc/seo-schema.php`, `inc/seo-hygiene.php`, `inc/sitemap.php` | `inc/breadcrumbs.php`, `inc/seo-cockpit/` |
| Contact form | `inc/contact.php`, `inc/forms/contact/` | `inc/contacts-admin.php`, `page-kontakt.php`, `inc/contact-local.php.example` |
| Newsletter | `inc/newsletter.php`, `inc/forms/newsletter/` | `inc/newsletter-broadcast.php`, `front-page.php` |
| Comments | `inc/comments.php` | `comments.php`, `assets/css/pages/single-editorial.css` |
| Votes | `inc/votes.php`, `inc/votes-api.php` | `assets/js/votes.js`, `assets/css/votes.css` |

## Avoid First

Do not open these unless the task directly needs them:

- `assets/js/d3-custom.min.js` - generated bundle.
- `fonts/*` - binary/static font assets.
- `_build-d3/node_modules/*` - dependency tree.
- `_stitch/*` - local tool artifacts.
- `style.css` full file - use `rg` for selectors/sections first.

## Current Hotspots

- `style.css`: 3,326 lines, still the largest active stylesheet and should be split further.
- `inc/graph-api.php`: graph build still does full post loading, but shared-topic edges use a topic-to-node map, glossary matching uses chunked term regexes sourced from `hp_glossar_get_term_index()`, and rebuilds stay on the scheduled `hp_graph_rebuild_event`.

## Context Budget Rules

- For narrow changes, read only the owner module plus the template/asset it touches.
- For architecture work, read `functions.php`, `inc/bootstrap.php`, `inc/manifest.php`, this file, `docs/ARCHITECTURE.md`, and targeted `rg` output.
- For CSS work, read `docs/CSS_INVENTORY.md` before opening large sections of `style.css`.
- Avoid pasting large PHP/CSS files into prompts. Use line-targeted reads.
- Prefer updating the docs when a module, asset handle, or REST route changes.

## Verification

Run PHP syntax check after PHP changes:

```sh
find . -name '*.php' -not -path './vendor/*' -not -path './_stitch/*' -not -path './_build-d3/*' -not -path './inc/contact-local.php' -print0 | xargs -0 -n 1 php -l
php scripts/check-manifest.php
php scripts/generate-wp-docs.php
git diff --exit-code -- docs/HOOKS.md docs/REST_ROUTES.md
```

Run D3 build only after `_build-d3/src/d3-custom.js` or `_build-d3/package.json` changes:

```sh
npm --prefix _build-d3 run build
```
