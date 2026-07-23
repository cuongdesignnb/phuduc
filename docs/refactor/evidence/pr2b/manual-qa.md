# PR 2B Manual QA Evidence

Environment: local Docker only, production build assets, temporary QA fixtures with slugs prefixed `qa-pr2b-*`.

## Final Filter Focus QA

Routes tested on `/san-pham` at 360px and 1024px:

- `?search=<101-character-string>`: PASS
- `?min_price=-1`: PASS
- `?max_price=-1`: PASS
- `?max_price=1000000000001`: PASS
- `?sort=invalid`: PASS
- `?min_price=1000&max_price=100`: PASS

Assertions for every scenario:

- HTTP=200
- ERROR_VISIBLE=YES
- RAW_VALUE_PRESERVED=YES
- INVALID_VALUE_NOT_APPLIED_TO_QUERY=YES
- CORRECT_FIELD_FOCUSED=YES
- NO_REDIRECT_LOOP=YES
- aria-invalid=true
- aria-describedby=<field-error-id>

Focus matrix:

- SEARCH_ERROR_FOCUS=PASS
- MIN_PRICE_ERROR_FOCUS=PASS
- MAX_PRICE_ERROR_FOCUS=PASS
- SORT_ERROR_FOCUS=PASS
- RANGE_ERROR_FOCUS=PASS
- INVALID_FILTERS_EXCLUDED_FROM_QUERY=PASS
- ERROR_SUMMARY_ROLE_ALERT=PASS
- ERROR_SUMMARY_ARIA_LIVE_POLITE=PASS

## Existing PR2B Browser QA Retained

- Product Index invalid price validation screenshot remains available: `screenshots/product-index-invalid-price.png`
- Product Index empty-state action uses `X?a b? l?c`: YES
- Product Detail H1 count: 1
- Product Gallery labels and pressed state: PASS
- Pagination labels/current/disabled states: PASS
- Review summary text: `??nh gi? ?? duy?t`
- Breadcrumb aria label: `?i?u h??ng ph?n c?p`
- About contact/no-contact QA: PASS
- Rich content malicious URL payloads removed: PASS

## Cleanup Policy

- QA_FIXTURES_REMOVED=YES
- TEMP_ADMIN_REMOVED=YES
- DATABASE_RESTORED_AFTER_QA=YES
- PRODUCTION_DATABASE_USED=NO
