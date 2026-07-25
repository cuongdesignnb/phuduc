# PR3C Route Matrix

| Area | Method | URI | Current route name | PR3C direction |
| --- | --- | --- | --- | --- |
| Order lookup | GET | `/tra-cuu-don-hang` | `order-lookup.index` | Keep bounded page envelope |
| Order lookup | POST | `/tra-cuu-don-hang` | `order-lookup.lookup` | Keep two-factor lookup and throttle |
| Warranty lookup | GET | `/tra-cuu-bao-hanh` | `warranty-lookup.index` | Keep bounded page envelope |
| Warranty lookup | POST | `/tra-cuu-bao-hanh` | `warranty-lookup.lookup` | Service lookup, phone fallback, throttle |
| Review submission | POST | `/danh-gia` | `reviews.store` | Add Form Request and `commerce-reviews` throttle |
| Admin orders | GET | `/admin/orders` | `admin.orders.index` | Canonical index DTO |
| Admin order detail | GET | `/admin/orders/{order}` | `admin.orders.show` | Canonical detail DTO |
| Admin order status | PATCH | `/admin/orders/{order}/status` | `admin.orders.updateStatus` | Versioned transition service |
| Admin reviews | GET | `/admin/reviews` | `admin.reviews.index` | Canonical moderation DTO |
| Admin review status | PATCH | `/admin/reviews/{review}/status` | `admin.reviews.updateStatus` | Versioned moderation service |
| Admin review delete | DELETE | `/admin/reviews/{review}` | `admin.reviews.destroy` | Policy guard; approved delete blocked |
| Admin warranties | resource | `/admin/warranties` | `admin.warranties.*` | Remove destroy; service-backed forms |
| Admin warranty void | PATCH | `/admin/warranties/{warranty}/void` | `admin.warranties.void` | Versioned void action |

All admin routes remain behind `auth` and `admin` middleware.
