# CSS Inventory

Purpose: make `style.css` navigable before splitting it. Line ranges are approximate and should be refreshed after large CSS moves.

## Current Files

| File | Lines | Status |
|---|---:|---|
| `style.css` | 7,147 | active global theme stylesheet |
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
| 1186-1201 | layout lines/container helpers | `layout/containers.css` |
| 1202-1591 | front page and entry modules | `pages/front-page.css` |
| 1592-1857 | mission page and portrait | `pages/mission.css` |
| 1858-2301 | contact page and form | `pages/contact.css` |
| 2302-2692 | newsletter forms and variants | `components/newsletter.css` |
| 2693-2912 | colophon/footer and empty states | `layout/footer.css` |
| 2913-3119 | essay/note single hero, body, footer | `pages/single-editorial.css` |
| 3120-3770 | essay end modules, comments, share | `features/editorial-engagement.css` |
| 3771-3904 | essay/note archives and responsive fallback | `pages/archives.css` |
| 3905-4507 | glossary terms, tooltips, glossary single, Begriff sections | `features/glossary.css` |
| 4508-5212 | dossier archive/single, GP wrapper reset, cite box | `features/dossier.css` |
| 5213-5292 | mini graph | `features/mini-graph.css` |
| 5293-5471 | glossary archive | fold into `features/glossary.css` |
| 5472-5605 | 404 page | `pages/error.css` |
| 5606-5760 | search form and search results | `pages/search.css` |
| 5761-5901 | topic archive | `pages/topic-archive.css` |
| 5902-5982 | related essays | `components/related.css` |
| 5983-6057 | previous/next navigation | `components/post-nav.css` |
| 6058-6105 | small modifiers, reduced motion, focus-visible | merge into owners/base |
| 6106-6178 | nav search toggle and overlay | `layout/header.css` |
| 6179-6913 | Wissensgraph page | `features/graph.css` |
| 6914-7147 | newsletter CTA pill and notification modal | `components/newsletter-modal.css` |

## Extracted CSS

| File | Handle | Owner/load condition |
|---|---|---|
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
