# Admin inventory

| Area | Route family | Current state | PR3A action |
|---|---|---|---|
| Dashboard | `/dashboard` | Query-heavy controller and legacy page | Replaced with service and canonical page contract |
| Products | `/admin/products` | Existing CRUD | Uses shared shell; no business refactor |
| Media | `/admin/media` | Existing library | Added to canonical navigation |
| Orders | `/admin/orders` | Existing CRUD and detail | Uses shared shell; no business refactor |
| Menus | `/admin/menus` | Existing CRUD | Uses shared shell; no business refactor |
| Homepage content | `/admin/home-content` | Existing section editor | Uses shared shell; no business refactor |
| Posts and categories | `/admin/posts`, `/admin/post-categories` | Existing CRUD | Uses shared shell; no business refactor |
| Reviews | `/admin/reviews` | Existing moderation | Uses shared shell; no business refactor |
| Warranties | `/admin/warranties` | Existing lookup/admin flow | Uses shared shell; no business refactor |
| Settings | `/admin/settings` | Existing configuration UI | Uses shared shell; no business refactor |

The foundation audit is intentionally scoped to the PR3A-owned shell, dashboard, services, middleware, and shared components. Legacy CRUD internals are recorded for follow-up rather than silently refactored here.
