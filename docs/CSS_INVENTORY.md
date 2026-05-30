# CSS Inventory

Purpose: make `style.css` navigable before splitting it. Line ranges are approximate and should be refreshed after large CSS moves.

## Current Files

| File | Lines | Status |
|---|---:|---|
| `style.css` | 6,879 | active global theme stylesheet |
| `assets/css/pages/front-page.css` | 508 | active conditional front page stylesheet |
| `assets/css/pages/legal.css` | 76 | active conditional legal pages stylesheet |
| `assets/css/votes.css` | 183 | active conditional votes stylesheet |
| `assets/css/diaspora-scroll.css` | 2,998 | deactivated/archived Diaspora page stylesheet |

## style.css Map

| Lines | Area | Suggested future owner |
|---:|---|---|
| 1-66 | theme header, design tokens | `base/tokens.css` |
| 67-144 | defensive hero/archive centering | merge into component/page owners |
| 145-240 | Wissensplattform `--wp-*` tokens | `base/tokens-knowledge.css` |
| 241-293 | Merriweather `@font-face` | `base/fonts.css` |
| 294-380 | base typography, layout, skip link | `base/base.css` |
| 381-1185 | site header, masthead, nav, prose utilities, TOC, topics, meta | split into `layout/header.css`, `components/prose.css`, `components/toc.css`, `components/meta.css` |
| 1186-1203 | layout lines/container helpers | `layout/containers.css` |
| 1204-1477 | mission page and portrait | `pages/mission.css` |
| 1478-1924 | contact page and form | `pages/contact.css` |
| 1925-2311 | newsletter forms and variants | `components/newsletter.css` |
| 2312-2532 | colophon/footer and empty states | `layout/footer.css` |
| 2533-3396 | essay/note single hero, body, footer, comments/share | split into `pages/single-editorial.css` + `features/editorial-engagement.css` |
| 3397-3520 | essay/note archives and responsive fallback | `pages/archives.css` |
| 3521-4240 | glossary terms, tooltips, glossary single, Begriff sections | `features/glossary.css` |
| 4241-5025 | dossier archive/single, GP wrapper reset, cite box, mini graph | split into `features/dossier.css` + `features/mini-graph.css` |
| 5026-5204 | glossary archive | fold into `features/glossary.css` |
| 5205-5341 | 404 page | `pages/error.css` |
| 5342-5493 | search form and search results | `pages/search.css` |
| 5494-5637 | topic archive | `pages/topic-archive.css` |
| 5638-5718 | related essays | `components/related.css` |
| 5719-5788 | previous/next navigation | `components/post-nav.css` |
| 5789-5911 | small modifiers, reduced motion, focus-visible, nav search toggle | merge into owners/base/layout |
| 5912-6646 | Wissensgraph page | `features/graph.css` |
| 6647-6879 | newsletter CTA pill and notification modal | `components/newsletter-modal.css` |

## Extracted CSS

| File | Handle | Owner/load condition |
|---|---|---|
| `assets/css/pages/front-page.css` | `hp-front-page` | `inc/enqueue.php`; `is_front_page()` |
| `assets/css/pages/legal.css` | `hp-legal-pages` | `inc/enqueue.php`; page templates/slugs `impressum`, `datenschutz` |

## Split Order

1. Extract page-only CSS first: contact, mission, 404, search, topic archive. Legal pages are already extracted.
2. Extract feature CSS with clear PHP owners: graph, glossary, dossier, mini-graph, newsletter.
3. Extract layout/components: header/nav, footer, TOC, prose, post nav, related.
4. Keep base tokens, fonts, base typography, and shared utilities in global CSS.

## Loading Strategy

Start without a build step:

- Keep `style.css` as theme metadata plus global base.
- Enqueue page/feature CSS conditionally from the owning PHP modules.
- Use `filemtime()` versions like the existing JS/CSS enqueues.
- Do not split archived Diaspora CSS until that page is reactivated or removed.

If request count becomes measurable, add a build step later and keep source partials as the editable files.

## Guardrails

- Move CSS in small commits by owner area.
- Do not rename selectors while moving code.
- After every extraction, verify the corresponding template/page in a browser.
- Update `docs/ASSET_MATRIX.md` whenever a new CSS file gets enqueued.
