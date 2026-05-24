# Architecture

This is a WordPress child theme. `functions.php` is the WordPress entry point. It loads `inc/bootstrap.php`, which reads `inc/manifest.php` and loads modules from `inc/` in dependency order.

## Loader Files

| File | Responsibility |
|---|---|
| `functions.php` | WordPress theme entry point, no business logic |
| `inc/bootstrap.php` | loads the module manifest and requires each module |
| `inc/manifest.php` | explicit module list in load order |

## Bootstrap Order

| Order | Module | Responsibility |
|---:|---|---|
| 1 | `inc/helpers.php` | reading time, body classes, search CPT inclusion, rewrite flush |
| 2 | `inc/post-types.php` | essay and note CPTs |
| 3 | `inc/taxonomies.php` | `topic` taxonomy, default topics, migrations |
| 4 | `inc/enqueue.php` | global/conditional assets, font preload, GP script defer |
| 5 | `inc/generatepress-compat.php` | GeneratePress meta/sidebar/copyright adjustments |
| 6 | `inc/meta-fields.php` | social teaser editor meta |
| 7 | `inc/seo-schema.php` | JSON-LD for site, essays, notes, glossary, dossiers, archives |
| 8 | `inc/seo-meta.php` | titles, descriptions, Open Graph, Twitter image metadata |
| 9 | `inc/seo-hygiene.php` | robots, redirects, head cleanup, headers, heartbeat, XML-RPC hardening |
| 10 | `inc/glossary.php` | glossary CPT, meta, editor UI, auto-linking, cache invalidation |
| 11 | `inc/link-preview.php` | REST preview data for internal links |
| 12 | `inc/dossier.php` | dossier CPT, meta, editor UI, relationships, citation box |
| 13 | `inc/breadcrumbs.php` | breadcrumb schema |
| 14 | `inc/header-nav.php` | custom header, menus, active nav state |
| 15 | `inc/comments.php` | editorial comment UX, validation, anti-spam, moderation |
| 16 | `inc/contacts-admin.php` | contact submission storage and admin/export UI |
| 17 | `inc/contact.php` | stable contact loader for `inc/forms/contact/*` |
| 18 | `inc/newsletter.php` | stable newsletter loader for `inc/forms/newsletter/*` |
| 19 | `inc/newsletter-broadcast.php` | optional publish broadcast to subscribers |
| 20 | `inc/privacy-maintenance.php` | scheduled cleanup for contact/newsletter retention |
| 21 | `inc/graph-api.php` | graph REST data, cache, graph page assets |
| 22 | `inc/mini-graph.php` | static SVG neighbor graph for single content |
| 23 | `inc/votes.php` | vote table and vote business logic |
| 24 | `inc/votes-api.php` | vote REST route and vote assets |
| 25 | `inc/glossar-seed.php` | one-time glossary/essay seed content |

## Content Model

| Type | Registration | Templates | Notes |
|---|---|---|---|
| `essay` | `inc/post-types.php` | `single-essay.php`, `archive-essay.php`, `template-parts/content-essay.php` | long-form content, reading time, schema |
| `note` | `inc/post-types.php` | `single-note.php`, `archive-note.php`, `template-parts/content-note.php` | shorter editorial notes |
| `glossar` | `inc/glossary.php` | `single-glossar.php`, `archive-glossar.php`, `template-parts/content-glossar.php` | glossary knowledge base, auto-link source |
| `dossier` | `inc/dossier.php` | `single-dossier.php`, `archive-dossier.php` | curated reading paths and citations |
| `page` | WordPress | `page-*.php` | mission, contact, graph, legal pages |
| `topic` taxonomy | `inc/taxonomies.php` | `taxonomy-topic.php` | topic pillar/archive pages |

## REST Routes

| Route | Method | Owner | Purpose |
|---|---|---|---|
| `/wp-json/hp/v1/graph` | GET | `inc/graph-api.php` | full graph data for Wissensgraph |
| `/wp-json/hp/v1/link-preview` | GET | `inc/link-preview.php` | preview payload for internal links |
| `/wp-json/hasim-org/v1/vote` | POST | `inc/votes-api.php` | like/dislike vote operations |

## Data Stores

| Store | Owner | Purpose |
|---|---|---|
| WordPress posts/postmeta | content modules | CPT content and feature metadata |
| `topic` terms | `inc/taxonomies.php` | topic grouping and pillar pages |
| `hp_newsletter_subscribers` | `inc/forms/newsletter/install.php`, `inc/forms/newsletter/subscribers.php` | local newsletter subscriptions |
| `hp_newsletter_suppressions` | `inc/forms/newsletter/install.php`, `inc/forms/newsletter/subscribers.php` | minimized unsubscribe suppression records |
| contact submissions table | `inc/contacts-admin.php` | local contact submission archive |
| votes table | `inc/votes.php` | like/dislike counts and user vote tracking |
| transients | graph/link/glossary modules | graph data, link previews, glossary caches |

## Known Architecture Debt

- `style.css` should be split into base, components, pages, and feature CSS.
- Graph cache versions should be separated from glossary cache versions.
- Editor inline JS should move to small editor assets when those panels are next touched.

## Migration Principle

Prefer migrations that preserve public `hp_*` function names and hook behavior. First move files or extract helpers, then change behavior in separate commits.
