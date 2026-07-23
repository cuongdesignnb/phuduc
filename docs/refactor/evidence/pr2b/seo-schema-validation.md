# SEO And Schema Validation

Automated coverage:
- `tests/Feature/Storefront/StorefrontSeoContractTest.php` validates product JSON-LD uses full review aggregate data.
- Product/detail/news/about contract tests validate page-level SEO data is delivered through the canonical `page` payload.
- `NewsIndexContractTest` validates valid category canonical URLs, invalid category 404 behavior, and search `noindex, follow`.
- Lighthouse SEO scores were captured for 5 QA routes in `lighthouse-summary.json`.

Lighthouse SEO minimums:
- `/san-pham`: 100
- `/san-pham/qa-pr2b-forklift-a`: 100
- `/tin-tuc`: 100
- `/tin-tuc/qa-pr2b-article-1`: 100
- `/gioi-thieu`: 92

Notes:
- Browser and Lighthouse validation were run after JavaScript render in Chrome headless.
- Production compressed retest remains required because local Docker QA was not served with production compression.
