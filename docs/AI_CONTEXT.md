# AI Context Map

Purpose: keep AI and human review focused. Start with this file, then open only the files relevant to the task.

## Repo Shape

- WordPress child theme for GeneratePress.
- `functions.php` loads feature modules from `inc/`.
- Active CSS is still concentrated in `style.css`.
- Frontend JS is split by feature under `assets/js/`.
- Generated/minified code and archived Diaspora assets should not be read for normal theme work.

## Read By Task

| Task | Start here | Usually also read |
|---|---|---|
| Bootstrap/module order | `functions.php` | `docs/ARCHITECTURE.md` |
| Asset loading | `inc/enqueue.php` | `inc/graph-api.php`, `inc/votes-api.php`, `docs/ASSET_MATRIX.md` |
| Header/navigation | `inc/header-nav.php` | `assets/js/nav.js`, header sections in `style.css` |
| Essay/note templates | `single-essay.php`, `single-note.php` | `assets/js/journal-single.js`, related CSS in `style.css` |
| Topic archive | `taxonomy-topic.php` | `inc/taxonomies.php`, related CSS in `style.css` |
| Glossary | `inc/glossary.php` | `single-glossar.php`, `archive-glossar.php`, `assets/js/link-preview.js` |
| Dossier | `inc/dossier.php` | `single-dossier.php`, `archive-dossier.php` |
| Wissensgraph | `inc/graph-api.php` | `assets/js/graph.js`, `page-wissensgraph.php`, `_build-d3/src/d3-custom.js` |
| Mini graph | `inc/mini-graph.php` | `inc/graph-api.php`, CSS in `style.css` |
| SEO/meta/schema | `inc/seo-meta.php`, `inc/seo-schema.php`, `inc/seo-hygiene.php` | `inc/breadcrumbs.php` |
| Contact form | `inc/contact.php` | `inc/contacts-admin.php`, `page-kontakt.php`, `inc/contact-local.php.example` |
| Newsletter | `inc/newsletter.php` | `inc/newsletter-broadcast.php`, `front-page.php` |
| Comments | `inc/comments.php` | `comments.php`, relevant CSS in `style.css` |
| Votes | `inc/votes.php`, `inc/votes-api.php` | `assets/js/votes.js`, `assets/css/votes.css` |

## Avoid First

Do not open these unless the task directly needs them:

- `assets/js/d3-custom.min.js` - generated bundle.
- `fonts/*` - binary/static font assets.
- `_build-d3/node_modules/*` - dependency tree.
- `_stitch/*` - local tool artifacts.
- `assets/css/diaspora-scroll.css`, `assets/js/diaspora-scroll.js`, `page-diaspora-architektur.php` - archived/deactivated Diaspora page.
- `style.css` full file - use `rg` for selectors/sections first.

## Current Hotspots

- `style.css`: 7k+ lines, should be split later.
- `inc/newsletter.php`: 1.8k+ lines, should be split by storage, mail, handlers, render, admin.
- `inc/contact.php`: 900+ lines, should be split by config, validation, mailer, handlers, page bootstrap.
- `inc/graph-api.php`: graph build combines topic edges and glossary regex scanning; optimize before major content growth.

## Context Budget Rules

- For narrow changes, read only the owner module plus the template/asset it touches.
- For architecture work, read `functions.php`, this file, `docs/ARCHITECTURE.md`, and targeted `rg` output.
- Avoid pasting large PHP/CSS files into prompts. Use line-targeted reads.
- Prefer updating the docs when a module, asset handle, or REST route changes.

## Verification

Run PHP syntax check after PHP changes:

```sh
find . -name '*.php' -not -path './_stitch/*' -not -path './_build-d3/*' -print0 | xargs -0 -n 1 php -l
```

Run D3 build only after `_build-d3/src/d3-custom.js` or `_build-d3/package.json` changes:

```sh
npm --prefix _build-d3 run build
```
