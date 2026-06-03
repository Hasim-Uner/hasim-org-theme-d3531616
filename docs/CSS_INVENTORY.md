# CSS Inventory

Purpose: make `style.css` navigable before splitting it. Line ranges are approximate and should be refreshed after large CSS moves.

## Current Files

| File | Lines | Status |
|---|---:|---|
| `style.css` | 3,326 | active global theme stylesheet |
| `assets/css/pages/front-page.css` | 508 | active conditional front page stylesheet |
| `assets/css/pages/mission.css` | 273 | active conditional mission page stylesheet |
| `assets/css/pages/error.css` | 133 | active conditional 404 stylesheet |
| `assets/css/pages/search.css` | 134 | active conditional search results stylesheet |
| `assets/css/pages/topic-archive.css` | 148 | active conditional topic archive stylesheet |
| `assets/css/pages/archives.css` | 69 | active conditional shared archive item/list stylesheet |
| `assets/css/pages/single-editorial.css` | 866 | active conditional essay/note single stylesheet |
| `assets/css/pages/contact.css` | 698 | active conditional contact page stylesheet |
| `assets/css/components/newsletter.css` | 634 | active conditional newsletter/header-modal stylesheet |
| `assets/css/components/related.css` | 80 | active conditional related entries stylesheet |
| `assets/css/components/post-nav.css` | 74 | active conditional previous/next navigation stylesheet |
| `assets/css/pages/wissensgraph.css` | 758 | active conditional Wissensgraph stylesheet |
| `assets/css/pages/legal.css` | 76 | active conditional legal pages stylesheet |
| `assets/css/votes.css` | 183 | active conditional votes stylesheet |

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
| extracted | mission page and portrait | `assets/css/pages/mission.css` |
| extracted | contact page and form | `assets/css/pages/contact.css` |
| extracted | newsletter forms, header CTA and modal | `assets/css/components/newsletter.css` |
| 1204-1423 | colophon/footer and empty states | `layout/footer.css` |
| extracted | essay/note single hero, body, footer, comments/share | `assets/css/pages/single-editorial.css` |
| 1424-1446 | shared prose headings | `components/prose.css` |
| extracted | shared archive header/list/item rules | `assets/css/pages/archives.css` |
| 1448-1470 | responsive base/header/front-page fallbacks | merge into owners/base/layout |
| 1472-2976 | glossary terms, tooltips, glossary single, Begriff sections, dossier archive/single, mini graph | split into `features/glossary.css`, `features/dossier.css`, `features/mini-graph.css` |
| 2977-3155 | glossary archive | fold into `features/glossary.css` |
| extracted | 404 page | `assets/css/pages/error.css` |
| 4096-4145 | shared search form used by header/search/404 | keep global or move to `components/search-form.css` if globally enqueued |
| extracted | search results page | `assets/css/pages/search.css` |
| extracted | topic archive | `assets/css/pages/topic-archive.css` |
| extracted | related essays | `assets/css/components/related.css` |
| extracted | previous/next navigation | `assets/css/components/post-nav.css` |
| 3207-3326 | small modifiers, reduced motion, focus-visible, nav search toggle | merge into owners/base/layout |
| extracted | Wissensgraph page | `assets/css/pages/wissensgraph.css` |

## Extracted CSS

| File | Handle | Owner/load condition |
|---|---|---|
| `assets/css/pages/front-page.css` | `hp-front-page` | `inc/enqueue.php`; `is_front_page()` |
| `assets/css/pages/mission.css` | `hp-mission-page` | `inc/enqueue.php`; Mission page template or slug `mission` |
| `assets/css/pages/error.css` | `hp-error-page` | `inc/enqueue.php`; `is_404()` |
| `assets/css/pages/search.css` | `hp-search-page` | `inc/enqueue.php`; `is_search()` |
| `assets/css/pages/topic-archive.css` | `hp-topic-archive` | `inc/enqueue.php`; `is_tax( 'topic' )` |
| `assets/css/pages/archives.css` | `hp-archives` | `inc/enqueue.php`; post type archives `essay`, `note`, `glossar` plus `topic` taxonomy archive |
| `assets/css/pages/single-editorial.css` | `hp-single-editorial` | `inc/enqueue.php`; singular `essay`, `note` |
| `assets/css/pages/contact.css` | `hp-contact-page` | `inc/enqueue.php`; Kontakt page template or slug `kontakt` |
| `assets/css/components/newsletter.css` | `hp-newsletter` | `inc/enqueue.php`; all frontend pages except Kontakt |
| `assets/css/components/related.css` | `hp-related` | `inc/enqueue.php`; singular `essay`, `note` |
| `assets/css/components/post-nav.css` | `hp-post-nav` | `inc/enqueue.php`; singular `essay`, `note` |
| `assets/css/pages/wissensgraph.css` | `hp-graph` | `inc/graph-api.php`; page slug `wissensgraph` |
| `assets/css/pages/legal.css` | `hp-legal-pages` | `inc/enqueue.php`; page templates/slugs `impressum`, `datenschutz` |

## Split Order

1. Extract remaining feature CSS with clear PHP owners: glossary, dossier, mini-graph.
2. Extract layout/components: header/nav, footer, TOC, prose.
3. Consider a globally enqueued `components/search-form.css` only if the header search form is split with the header bundle.
4. Keep base tokens, fonts, base typography, and shared utilities in global CSS.

## Loading Strategy

Start without a build step:

- Keep `style.css` as theme metadata plus global base.
- Enqueue page/feature CSS conditionally from the owning PHP modules.
- Use `filemtime()` versions like the existing JS/CSS enqueues.

If request count becomes measurable, add a build step later and keep source partials as the editable files.

## Guardrails

- Move CSS in small commits by owner area.
- Do not rename selectors while moving code.
- After every extraction, verify the corresponding template/page in a browser.
- Update `docs/ASSET_MATRIX.md` whenever a new CSS file gets enqueued.
