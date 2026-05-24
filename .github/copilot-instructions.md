# Copilot Instructions: Hasimuener Journal Theme

## Project

Hasimuener Journal is a WordPress child theme for hasimuener.org, extending GeneratePress. It is an editorial publication theme for long-form political and social analysis, not a marketing site or portfolio.

- Parent theme: GeneratePress
- Runtime: WordPress, PHP hooks/actions, CSS, vanilla JavaScript
- Bootstrap: `functions.php` loads modules from `inc/`
- Naming: custom PHP functions use `hp_`; custom CSS classes use `hp-`; custom CSS properties use `--hj-`

## First Files To Read

For orientation, read these small docs before opening large files:

- `docs/AI_CONTEXT.md` - task-to-file map and context budget
- `docs/ARCHITECTURE.md` - modules, data flows, REST routes
- `docs/ASSET_MATRIX.md` - frontend assets, handles, load conditions
- `plans/repo-architektur-effizienz-2026.md` - current architecture improvement plan

Avoid reading generated/minified or archived files unless directly needed:

- `assets/js/d3-custom.min.js`
- `assets/css/diaspora-scroll.css`
- `assets/js/diaspora-scroll.js`
- `page-diaspora-architektur.php`
- `fonts/*`
- `_build-d3/node_modules/*`
- `_stitch/*`

## Active Architecture

`functions.php` is intended to contain no business logic. It currently loads these module groups:

- Core: `helpers.php`, `enqueue.php`, `generatepress-compat.php`, `header-nav.php`
- Content: `post-types.php`, `taxonomies.php`, `glossary.php`, `dossier.php`, `glossar-seed.php`
- SEO: `seo-meta.php`, `seo-schema.php`, `seo-hygiene.php`, `breadcrumbs.php`
- Forms/admin: `contact.php`, `contacts-admin.php`, `newsletter.php`, `newsletter-broadcast.php`, `privacy-maintenance.php`
- Graph/engagement: `graph-api.php`, `mini-graph.php`, `comments.php`, `votes.php`, `votes-api.php`
- Editor meta: `meta-fields.php`

Large files that should be touched carefully:

- `style.css` - all active global CSS, currently very large
- `inc/newsletter.php` - newsletter storage, mail, handlers, admin UI
- `inc/contact.php` - contact page, validation, Brevo/wp_mail handling
- `inc/glossary.php` - glossary CPT, meta, editor UI, auto-linking

## Editorial Constraints

All changes should support serious, accessible reading:

- Prioritize reading flow for long-form essays.
- Keep semantic HTML and logical heading structure.
- Maintain WCAG AA contrast and keyboard usability.
- Treat structured data, timestamps, citations, and metadata as part of the editorial product.
- Use structural and process-oriented wording for political/commemorative content.

## Implementation Rules

- Extend GeneratePress only through hooks and filters.
- Do not edit parent theme files.
- Keep functions prefixed with `hp_`.
- Register hooks at the bottom of each module.
- Prefer small, domain-specific modules over adding more code to the largest files.
- Use conditional asset loading. Do not enqueue feature JS/CSS globally unless it is used globally.
- Preserve existing public function names unless doing a deliberate migration.

## Assets

Current active global frontend assets:

- `style.css` via `hp-journal-style`
- `assets/js/nav.js` via `hp-nav-js`

Current conditional assets:

- `assets/js/journal-single.js` on singular essay/note/post
- `assets/js/link-preview.js` on singular essay/note/post/glossar/dossier/page
- `assets/js/d3-custom.min.js` and `assets/js/graph.js` on `/wissensgraph/`
- `assets/js/votes.js` and `assets/css/votes.css` on essay/note singles and archives

`assets/js/journal.js` and full `assets/js/d3.min.js` are no longer active.

## Common Workflows

Adding a module:

1. Create an `inc/*.php` file with `defined( 'ABSPATH' ) || exit;`.
2. Keep one clear responsibility per module.
3. Register hooks at the bottom.
4. Add `require_once` in `functions.php` in dependency order.

Adding styles:

1. Prefer existing `--hj-` design tokens.
2. Keep selectors scoped to `hp-` classes where possible.
3. Avoid unrelated CSS cleanup in feature changes.
4. For new feature CSS, consider a conditional asset instead of growing `style.css`.

Adding JavaScript:

1. Use vanilla JS unless the existing feature depends on a library.
2. Enqueue only on pages where needed.
3. Use `wp_localize_script()` only for runtime data passed from PHP.

## Verification

Baseline check:

```sh
find . -name '*.php' -not -path './_stitch/*' -not -path './_build-d3/*' -print0 | xargs -0 -n 1 php -l
```

For D3 bundle changes:

```sh
npm --prefix _build-d3 run build
```
