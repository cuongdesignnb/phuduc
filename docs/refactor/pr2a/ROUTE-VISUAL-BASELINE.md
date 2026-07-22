# PR 2A Route and Visual Baseline

## Public route inventory

| Route | Name | PR 2A treatment |
| --- | --- | --- |
| `/` | `home` | Full Homepage and global shell migration |
| `/san-pham` | `products.index` | Header search destination; page redesign deferred to PR 2B |
| `/san-pham/{slug}` | `products.show` | Global shell/token compatibility only; redesign deferred to PR 2B |
| `/tin-tuc` | `news.index` | Global shell/token compatibility only; redesign deferred to PR 2B |
| `/tin-tuc/{slug}` | `news.show` | Global shell/token compatibility only; redesign deferred to PR 2B |
| `/gioi-thieu` | `about` | Global shell/token compatibility only; redesign deferred to PR 2B |
| Cart, checkout, success, order lookup, warranty lookup | Multiple | Global shell/token compatibility only; redesign deferred to PR 2C |
| Login/profile/dashboard | Multiple | Account destination verification only |

## Homepage baseline capture plan

The unchanged base is captured under `docs/refactor/evidence/pr2a/baseline/` at:

- `home-360x800.png`
- `home-390x844.png`
- `home-768x1024.png`
- `home-1440x1000.png`
- `home-1920x1080.png`
- `header-desktop.png`
- `header-tablet.png`
- `header-mobile-menu-open.png`
- `footer-desktop.png`
- `home-default-primary.png`
- `home-changed-primary.png`

## Baseline interaction expectations

| Interaction | Baseline result |
| --- | --- |
| Desktop search Enter/button | No submit handler; does nothing |
| Mobile search Enter/button | No submit handler; does nothing |
| Category selector | No action or menu |
| Desktop item with children | Chevron only; children unavailable |
| Mobile item with children | Flat parent link; children unavailable |
| Escape/click outside | No menu-specific handling |
| Authenticated non-admin account | Incorrectly targets Admin dashboard |
| Footer groups | At most first two groups rendered |
| Newsletter | Input/button have no endpoint |
| Primary color update | Volt runtime may update legacy utilities, but hardcoded Header/Home colors remain unchanged |
| Initial theme | Blade and JavaScript derive separate Volt palettes; visual consistency is not guaranteed |

## Baseline responsive risks

- Desktop Header reserves a 310px branding area and shows search/actions only from `xl`, producing a large behavioral jump.
- Tablet receives only logo and menu toggle; there is no persistent search or cart action.
- Footer uses a 2040px container and a fixed five-column template rather than adapting to group count.
- Header/Footer and Homepage use different fixed widths.
- Mobile menu has no viewport-height/scroll/body-lock strategy.
- Critical visual state and contrast are not governed by one theme contract.
