# PR 2A Manual QA

QA date: 2026-07-21

Environment: local Docker application and MySQL, production Vite manifest

Final state: QA fixtures removed, original settings restored, Docker stopped

## Theme runtime

| Check | Result | Evidence |
| --- | --- | --- |
| Default `#ffd400` | PASS | Correct server and runtime tokens; readable dark foreground |
| Blue `#2563eb` | PASS | Header search and shared actions updated; white foreground |
| Dark `#111827` | PASS | Readable white foreground |
| Very light `#ffffff` | PASS | Readable dark foreground |
| Direct reload / no blank theme state | PASS | Tokens exist before module script; watcher tolerates initial missing props |
| Inertia navigation | PASS | Theme persists; one theme style and one font link |
| Duplicate style/font nodes | PASS | `#storefront-theme=1`, `#storefront-theme-fonts=1` |
| Contrast | PASS | Unit matrix covers primary/hover/active and Lighthouse contrast audit passes |

## Header and navigation

| Check | Result | Notes |
| --- | --- | --- |
| Desktop search button | PASS | Navigates to `/san-pham?search=xe+nang`; query preserved |
| Desktop search Enter | PASS | Explicit Enter submission verified with `pin` |
| Mobile search Enter | PASS | Verified with `ắc quy` |
| Desktop nested menu | PASS | Parent, child and grandchild rendered; click-outside closes |
| Mobile nested menu | PASS | Two accordion levels and third-level link rendered |
| Escape | PASS | Closes desktop dropdown/mobile drawer and restores body scroll |
| Active navigation | PASS | Active route style uses accessible brand text token |
| Guest account | PASS | `/login` |
| Non-admin account | PASS | `/profile` |
| Admin account | PASS | `/dashboard` |
| Cart | PASS | Real count and quantity-aware aria-label |
| Dead category control | PASS | Removed |

## Footer

| Check | Result | Notes |
| --- | --- | --- |
| All valid footer groups | PASS | No `slice(0, 2)` limit |
| Social links | PASS | Only populated links; external security attributes present |
| Contact fields | PASS | Phone/email/address/working hours conditional |
| Long address | PASS | Wrapped without overflow |
| Missing logo | PASS | Site-name wordmark fallback |
| Missing phone/email | PASS | No empty `tel:` or `mailto:` links |
| Fake newsletter | PASS | Removed |
| Mobile layout | PASS | No horizontal overflow |

## Homepage and variants

All ten registered sections were exercised with local QA fixtures:

1. hero
2. category_cards
3. benefit_strip
4. featured_products
5. energy_banner
6. industry_solutions
7. testimonials
8. partners
9. latest_posts
10. consultation_steps

Variant checks:

- Hero: `industrial_marketplace`, `split` — PASS
- Category cards: `cards`, `compact_cards` — PASS
- Featured products: `marketplace_grid`, `compact_grid` — PASS
- Latest posts: `editorial_grid`, `compact_grid` — PASS
- Remaining registry variants map to explicit section layouts — PASS
- Shared ProductCard and NewsCard render canonical PR 1 contracts — PASS
- Critical Hero/H1 content is visible without IntersectionObserver — PASS
- Missing images use neutral accessible placeholders, never fake product art — PASS

## Responsive matrix

| Width | Horizontal overflow | Header | Cards/sections | Footer |
| ---: | --- | --- | --- | --- |
| 360 | PASS | PASS | PASS | PASS |
| 390 | PASS | PASS | PASS | PASS |
| 768 | PASS | PASS | PASS | PASS |
| 1024 | PASS | PASS | PASS | PASS |
| 1280 | PASS | PASS | PASS | PASS |
| 1440 | PASS | PASS | PASS | PASS |
| 1920 | PASS | PASS | PASS | PASS |

## Accessibility

- Skip link and `#main-content`: PASS
- Focus-visible styling: PASS
- Keyboard search/navigation/Escape: PASS
- Semantic nav, dropdown state and icon labels: PASS
- Image-link accessible names: PASS
- Reduced motion fallback: PASS
- Lighthouse accessibility: 100 mobile / 100 desktop
- Lighthouse console, contrast, link-name and meta-description audits: PASS

## Lighthouse production build

Three runs were used for each form factor because local performance varied.

| Form factor | Performance runs | Median | Accessibility | Best Practices | SEO | Median CLS |
| --- | --- | ---: | ---: | ---: | ---: | ---: |
| Mobile | 62, 70, 68 | 68 | 100 | 100 | 100 | 0.0001 |
| Desktop | 93, 94, 96 | 94 | 100 | 100 | 100 | 0.0007 |

The mobile Performance target of 85 is not met on the local PHP development server.
The median audit attributes most avoidable delay to uncompressed production assets
and unused CSS/JS transfer under simulated mobile throttling. No dependency or scope
expansion was made to game the score. A production web server with HTTP compression
should be validated separately before deployment.

## Cleanup

- QA user/session removed.
- Temporary menus/menu items removed (database was empty before QA).
- Temporary products/posts/home items/sections removed.
- `site.primary_color` restored to `#ffd400`.
- Original homepage variants restored.
- Storefront reloaded as guest with no QA strings.
- `docker compose down` completed; `docker compose ps` is empty.
