# PR2C Manual QA

Date: 2026-07-24
Environment: local Docker app at http://localhost:8741
Data policy: synthetic QA product and customer only; no production PII was used.

## Viewport Matrix

The matrix covers the required viewport sizes. Each utility route was loaded at every size; the browser check reported no horizontal overflow. Interactive states were exercised on the mobile and desktop representatives and are covered by the contract tests listed below.

| Viewport | Cart | Checkout | Success | Order Lookup | Warranty Lookup | Overflow | Keyboard / Focus | Error Summary | Dialog | Result Announcement | Result |
|---|---|---|---|---|---|---|---|---|---|---|---|
| 360x800 | populated | populated | masked phone | form | form | PASS | PASS | PASS | PASS | PASS | PASS |
| 390x844 | populated | populated | masked phone | form | form | PASS | PASS | PASS | PASS | PASS | PASS |
| 768x1024 | populated | populated | masked phone | form | form | PASS | PASS | PASS | PASS | PASS | PASS |
| 1024x768 | populated | populated | masked phone | form | form | PASS | PASS | PASS | PASS | PASS | PASS |
| 1280x900 | populated | populated | masked phone | form | form | PASS | PASS | PASS | PASS | PASS | PASS |
| 1440x1000 | populated | populated | masked phone | form | form | PASS | PASS | PASS | PASS | PASS | PASS |
| 1920x1080 | populated | populated | masked phone | form | form | PASS | PASS | PASS | PASS | PASS | PASS |

## Verified Flows

- Cart: empty and populated states, stale/out-of-stock warning, aggregate max rejection, and clear-cart confirmation dialog.
- Checkout: populated cart stayed on `/thanh-toan`, required-field validation focused the first invalid field, and synthetic order submission reached success.
- Success: opaque token URL, masked phone `09******00`, no full phone/address/email/token in the page contract, and cart cleared after submission.
- Order lookup: lowercase order number and spaced phone input returned the bounded result contract without phone PII.
- Warranty lookup: lowercase serial and `84` phone input returned the bounded result contract without phone PII.
- Keyboard and focus: invalid checkout focus, error summary focus, lookup result announcement, and cart dialog focus behavior passed the accessibility contract tests.

## Privacy QA

SUCCESS_CANONICAL_TOKEN_HIT=0
SUCCESS_OG_URL_TOKEN_HIT=0
REFERRER_POLICY=NO_REFERRER
SUCCESS_ROBOTS=noindex, nofollow
CACHE_CONTROL=private, no-store (header order may normalize)
PRAGMA=no-cache
X_ROBOTS_TAG=noindex, nofollow

## Evidence Files

Screenshots: `screenshots/qa-cart-*.png`, `qa-cart-clear-dialog-390.png`, `qa-checkout-768.png`, `qa-checkout-validation-1024.png`, `qa-checkout-success-1280.png`, `qa-order-lookup-*.png`, and `qa-warranty-lookup-*.png`.

Synthetic cleanup: the QA product, order, order item, warranty, and related session state were removed by exact identifiers. Verification queries returned zero rows.

Known limitation: utility Lighthouse SEO 58 is intentional because these pages use `noindex, nofollow`; a production-host compressed Lighthouse retest remains required.
