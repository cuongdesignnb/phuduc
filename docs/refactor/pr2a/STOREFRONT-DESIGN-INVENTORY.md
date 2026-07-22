# PR 2A Storefront Design Inventory

## Baseline

- Base branch: `main`
- Base SHA: `ead76be1deec5c46b19c971403aeb6ff21604429`
- Audit date: 2026-07-21
- Public scope: `GuestPageLayout`, `Components/Home`, and `Pages/Guest`

## Token and runtime findings

| Area | Baseline finding | PR 2A disposition |
| --- | --- | --- |
| `tailwind.config.js` | Brand colors are fixed; public and legacy Admin namespaces are mixed. | Map public `brand`, `surface`, `content`, and `line` namespaces to canonical CSS variables. Keep documented Admin aliases only. |
| `resources/css/app.css` | Fixed Google Font import; fixed palette variables; neon, glass, glow, tilt, particle, and hidden reveal utilities. | Split tokens/storefront/Admin compatibility; remove fixed font import and public legacy visual foundation. |
| `resources/views/app.blade.php` | A second HEX/HSL palette algorithm generates `--volt-*` variables. | Render only the server-normalized variable map. |
| `resources/js/app.js` | A global mixin initializes color and font composables in every component; progress color is hardcoded. | Initialize one theme runtime at the Inertia root. |
| `useColorLoader.js` | A third palette algorithm generates `--volt-*` variables in JavaScript. | Replace with canonical variable application; no client-side color derivation. |
| `useFontLoader.js` | Reads legacy global props and can add duplicate Google Font requests. | Fold whitelisted font updates into the single theme runtime. |
| `SiteConfigurationService` | Exposes `primary_color` and `fonts`, but no canonical theme object. | Add `site.theme`; retain legacy compatibility keys temporarily. |

## Static count before refactor

Counts cover `resources/js/Layouts/GuestPageLayout.vue`, `resources/js/Components/Home`, and `resources/js/Pages/Guest`.

| Pattern | Count |
| --- | ---: |
| `#ffd400` | 10 |
| `#f3c800` | 1 |
| `#d49d00` | 4 |
| `#e4a900` | 1 |
| `#f1b900` | 1 |
| `glass-card` | 20 |
| `neon-line` | 6 |
| `text-gradient` | 3 |
| `max-w-[1780px]` | 11 |
| `max-w-[2040px]` | 1 |
| Component-local `IntersectionObserver` | 4 |

No public `bg-volt-*`, `text-volt-*`, or `border-volt-*` use was found at baseline, although the configuration and color runtime still expose Volt as a foundation.

## Global shell findings

- `GuestPageLayout.vue` owns site data, navigation normalization, cart calculation, flash handling, full Header markup, mobile menu, full Footer markup, and newsletter markup.
- Desktop and mobile search inputs do not submit.
- The category selectors are buttons without a category contract or action.
- Desktop navigation displays child indicators but does not render child menus.
- Mobile navigation does not render child accordions.
- Internal fallback navigation uses `#` for missing URLs.
- All authenticated users are sent to the Admin dashboard, including non-admin users.
- The phone link falls back to `href="#"` when no number exists.
- Footer renders only `footerGroups.slice(0, 2)`.
- Newsletter input and button have no route or subscriber backend.
- Header/Footer use unrelated container widths and hardcoded colors.
- Header and Footer have no complete keyboard, escape, focus, drawer-scroll, or external-link behavior.

## Homepage findings

- Ten registry-backed sections exist and are routed through `HomeSectionRenderer`.
- The sections duplicate container, heading, card, button, image, and link markup.
- Hero, benefit strip, energy banner, and testimonials hardcode the default brand color.
- Category, featured product, and latest post sections use Amber classes rather than canonical brand tokens.
- Product and news card markup is duplicated and route-coupled inside collection sections.
- Variants supplied by the section contract are not rendered as materially different layouts.
- Category/solution items silently fall back to the product index when their URL is missing.
- Visual icon fallbacks may render arbitrary strings or a check character instead of a controlled icon map.
- Layout width is primarily `max-w-[1780px]`; other public pages use `max-w-7xl`/`max-w-5xl` and the Footer uses `max-w-[2040px]`.

## Deferred-page compatibility findings

Catalog, content, commerce, and utility pages belong to PR 2B/2C, but the PR 2A static audit includes `Pages/Guest`. Compatibility-only class replacement is therefore allowed in PR 2A where required to remove forbidden hardcoded brand/neon/glass names. Page business behavior and page-specific redesign remain deferred.

## Reusable candidates

- Shell: `StorefrontHeader`, `StorefrontFooter`, `DesktopNavigation`, `MobileNavigation`, `StorefrontSearch`.
- Layout: `StorefrontContainer`, `PageHero`, `SectionHeader`.
- UI: `UiButton`, `UiCard`, `StatusBadge`, `EmptyState`, `FormField`, `QuantityStepper`.
- Media/data: `ResponsiveImage`, `ProductCard`, `NewsCard`, `Pagination`, `Breadcrumbs`.
- Runtime: one canonical theme/font runtime at application root.

## Dead controls and removals

- Remove category dropdown/button until a real public category contract exists.
- Remove the newsletter form because no subscriber endpoint exists.
- Do not render missing item URLs as `#` or substitute unrelated destinations.
- Remove critical-content hidden reveal behavior and duplicated per-page observers from the PR 2A foundation.
