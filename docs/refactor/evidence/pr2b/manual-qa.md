# PR 2B Manual QA Evidence

Environment: local Docker only, production build assets, temporary QA fixtures with slugs prefixed `qa-pr2b-*`.

## Browser QA

- Product Index invalid price validation: PASS
- Invalid price screenshot: `docs/refactor/evidence/pr2b/screenshots/product-index-invalid-price.png`
- Min/max values retained after validation: YES
- Validation message visible as plain text: YES
- `aria-invalid` on first invalid field: YES
- `aria-describedby=min-price-error`: YES
- Focus moved to first invalid field: YES
- Product Index empty-state action uses `X?a b? l?c`: YES
- Product Detail H1 count: 1
- Product Gallery aria label: `Th? vi?n ?nh c?a <product>`
- Product Gallery thumbnail labels: `Ch?n h?nh 1`, `Ch?n h?nh 2`
- Product Gallery selected thumbnail `aria-pressed=true`: YES
- Pagination aria label: `Ph?n trang`
- Pagination labels: `Tr??c`, `Sau`
- Pagination disabled/current states: `aria-disabled=true`, `aria-current=page`
- Pagination `href="#"`: NO
- Review summary text: `??nh gi? ?? duy?t`
- Breadcrumb aria label: `?i?u h??ng ph?n c?p`
- About mission/vision/contact labels: PASS
- About without contact data hides contact section: PASS
- Rich content malicious URL payloads removed from rendered anchors/images: PASS

## Viewports

- 360px Product Index screenshot: `screenshots/product-index-360.png`
- 1024px Product Index screenshot: `screenshots/product-index-1024.png`
- 1920px Product Index screenshot: `screenshots/product-index-1920.png`
- Previous seven-viewport QA evidence remains present for PR 2B.

## Cleanup Policy

- QA fixtures are temporary local Docker data only.
- Temporary Admin created: NO
- Production database used: NO
- SQL dump restoration required after QA: YES
