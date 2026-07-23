# Manual QA

Date: 2026-07-23
Environment: Docker app at http://localhost:8741
Data policy: synthetic QA data only; no production PII was used.

Verified screenshots:

- Cart empty, populated desktop, populated mobile, and stale/out-of-stock warning.
- Checkout desktop and mobile layouts.
- Browser required-field validation state.
- Checkout success desktop and mobile with an opaque public token.
- Order lookup form and successful two-factor result.
- Warranty lookup form and successful two-factor result.

Synthetic values used: QA Synthetic Cart Item, Synthetic Customer, 0900000000, synthetic@example.test, Synthetic address, QA-SYNTHETIC-SERIAL.

Viewport checks: default desktop, 1440x900, and 390x844.

Cleanup: the synthetic product, order, order item, warranty, and related QA cart state were removed by exact identifiers. Verification queries returned zero rows.

Limitation: Lighthouse CLI was not installed and was not fetched or added. No Lighthouse scores are claimed.
