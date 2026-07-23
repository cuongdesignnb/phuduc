# PR2B Catalog And Content Inventory

## Scope

Routes:

- `/san-pham`
- `/san-pham/{slug}`
- `/tin-tuc`
- `/tin-tuc/{slug}`
- `/gioi-thieu`

## Backend Findings

- `Guest\ProductController@index` returns a raw Eloquent paginator as `products`.
- `Guest\ProductController@show` returns a raw Product model as `product`.
- `Guest\ProductController@show` uses `inRandomOrder()` for related products.
- Product detail loads approved reviews with `limit(20)` and SEO aggregate can be derived from that limited collection.
- Product index search interpolates user input into `LIKE` without escaping `%` or `_`.
- Product index does not support deterministic sort modes beyond `latest()`.
- Product index loads image relation in catalog instead of a normalized card contract.
- `Guest\NewsController@index` returns a raw Eloquent paginator as `posts`.
- `Guest\NewsController@show` returns a raw Post model as `post`.
- News index search only checks title and does not escape `LIKE` wildcards.
- News related posts are deterministic by `latest()` but need explicit `id` tie-breaker.
- `Guest\PageController@about` queries `about.%` and `site.%`, then returns raw settings.
- `SeoService` directly queries settings and builds direct `storage/` URLs.

## Frontend Findings

- Product index and detail build image URLs with `'/storage/' + path`.
- News index and detail build image URLs with `'/storage/' + path`.
- Product index and detail use client-side `formatPrice`.
- Product detail and news pages use `new Date(...).toLocaleDateString(...)`.
- News detail uses `$fixText`.
- Product index, product detail, news index, and about use local `IntersectionObserver`.
- Product detail hardcodes `tel:1900xxxx`.
- Product and news pages duplicate card markup instead of shared Storefront cards.
- Pagination uses raw Inertia links with `href="#"` fallbacks or `v-html` labels.
- Rich HTML is rendered directly with `v-html` outside a dedicated boundary.

## Data And Media

- Local database is restored from `phuduc_2026-07-22_171914.sql`.
- Production database is not used.
- Media backup is not restored, so pages need neutral missing-image states.
- `public/storage` exists from previous Docker setup.
