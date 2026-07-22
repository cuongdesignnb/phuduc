# PR 2A Component Migration Matrix

| Baseline owner | Responsibility/problem | PR 2A target | Scope |
| --- | --- | --- | --- |
| `GuestPageLayout.vue` | Full Header/Footer, data normalization, flash, menu, search | Thin layout using `StorefrontHeader` and `StorefrontFooter` | Rebuild |
| Header markup | Branding, dead category controls, fake search, account/cart | `StorefrontHeader` + `StorefrontSearch` | Rebuild |
| Header nav loop | Indicates children without menus | `DesktopNavigation` | Rebuild |
| Mobile nav loop | Flat list without child handling/focus lock | `MobileNavigation` accordion drawer | Rebuild |
| Footer markup | Two-group cap and fake newsletter | `StorefrontFooter` rendering all valid groups/contact/social | Rebuild |
| Repeated max-width wrappers | 1780/2040/7xl/5xl mix | `StorefrontContainer` shell/content variants | Foundation |
| Repeated section headings | Local h2/action patterns | `SectionHeader` | Homepage |
| Repeated links/buttons | Hardcoded brand/Amber button styles | `UiButton` | Foundation/Homepage |
| Repeated cards | Mixed white/glass/product/news cards | `UiCard` | Foundation |
| Product collection markup | Duplicated card, local presentation | `ProductCard` consuming PR 1 contract | Homepage |
| Post collection markup | Duplicated card, route-coupled presentation | `NewsCard` consuming PR 1 contract | Homepage |
| Raw images | Inconsistent aspect/loading/fallback | `ResponsiveImage` | Foundation/Homepage |
| Hero section | One layout regardless of selected variant | Industrial marketplace and split layouts | Homepage |
| Category cards | One layout, false fallback links | Cards and compact cards; non-link item fallback | Homepage |
| Benefit strip | Fixed visual and icon fallback | Semantic responsive strip | Homepage |
| Featured products | One grid and duplicated card | Marketplace and compact grids using `ProductCard` | Homepage |
| Energy banner | Fixed yellow/blue styling | Brand/surface-token banner | Homepage |
| Industry solutions | Arbitrary tones/fallback route | Whitelisted semantic tones, neutral fallback | Homepage |
| Testimonials | Fixed accent and ad-hoc cards | Semantic quote grid/scroll layout | Homepage |
| Partners | Basic logo loop | Reserved-ratio logo cards with optional links | Homepage |
| Latest posts | One grid and duplicated card | Editorial and compact grids using `NewsCard` | Homepage |
| Consultation steps | Ad-hoc numbered cards | Ordered responsive sequence | Homepage |
| `useColorLoader.js` | Client palette generation | `useThemeRuntime.js` canonical apply | Replace |
| `useFontLoader.js` | Per-component font loader | Theme runtime font-link update | Replace |
| Guest legacy glass/neon classes | Forbidden names included by audit | Semantic compatibility classes only | Mechanical PR 2A boundary |
| `Breadcrumbs`, `Pagination`, `EmptyState`, `StatusBadge`, `FormField`, `QuantityStepper` | Missing reusable APIs for later pages | Create stable prop-driven primitives | Foundation; full adoption deferred |

## Dependency rule

Storefront primitives accept explicit props and must not read the whole Inertia page when those props are sufficient. Page/business queries remain in controllers/services. Product and news cards do not reconstruct media paths, prices, ratings, or specifications.
