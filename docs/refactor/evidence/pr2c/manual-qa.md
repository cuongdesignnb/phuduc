# Manual QA

Date: 2026-07-23
Environment: Docker app at http://localhost:8741
Data policy: synthetic QA data only; no production PII was used.

Verified flows:

- Cart empty, populated state, stale/out-of-stock warning, aggregate max rejection, and clear-cart confirmation dialog.
- Checkout populated state, required-field validation with focus on the first invalid field, successful order, numeric money display, and privacy-safe success result.
- Order lookup with lowercase order number and spaced phone input; result returned without phone PII.
- Warranty lookup with lowercase serial and `84` phone input; result returned without phone PII.

Viewport matrix captured: 360x800, 390x844, 768x1024, 1024x768, 1280x900, 1440x1000, and 1920x1080.

Screenshots: `screenshots/qa-cart-*.png`, `qa-cart-clear-dialog-390.png`, `qa-checkout-768.png`, `qa-checkout-validation-1024.png`, `qa-checkout-success-1280.png`, `qa-order-lookup-*.png`, and `qa-warranty-lookup-*.png`.

Synthetic values used: QA Synthetic Cart Item, QA Synthetic Customer, 0900000000, qa.synthetic@example.test, QA Synthetic Address, and QA-SYNTH-SERIAL.

Cleanup: the synthetic product, order, order item, warranty, and related QA cart state were removed by exact identifiers. Verification queries returned zero rows.

Known QA limitation: the Lighthouse utility-page SEO score is intentionally reduced by `noindex, nofollow`; the empty-session checkout route redirects to cart.
