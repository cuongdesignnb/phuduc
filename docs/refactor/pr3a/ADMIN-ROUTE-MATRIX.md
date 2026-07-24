# Admin route matrix

| Navigation key | Route | Middleware | Permission contract |
|---|---|---|---|
| dashboard | `dashboard` | `auth`, `verified`, `admin` | `admin.dashboard.view` |
| products | `admin.products.index` | `auth`, `admin` | `admin.products.view` |
| media | `admin.media.index` | `auth`, `admin` | `admin.media.view` |
| orders | `admin.orders.index` | `auth`, `admin` | `admin.orders.view` |
| menus | `admin.menus.index` | `auth`, `admin` | `admin.menus.view` |
| home_content | `admin.home-content.index` | `auth`, `admin` | `admin.home_content.view` |
| posts | `admin.posts.index` | `auth`, `admin` | `admin.posts.view` |
| post_categories | `admin.post-categories.index` | `auth`, `admin` | `admin.post_categories.view` |
| reviews | `admin.reviews.index` | `auth`, `admin` | `admin.reviews.view` |
| warranties | `admin.warranties.index` | `auth`, `admin` | `admin.warranties.view` |
| settings | `admin.settings.index` | `auth`, `admin` | `admin.settings.view` |

The navigation service is the single source for labels, route names, icons, active patterns, and permission keys.
