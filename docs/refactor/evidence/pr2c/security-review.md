# Security Review

- Checkout intent is opaque, session-bound, and idempotent.
- Checkout locks product rows in deterministic ID order before validating stock and pricing.
- Order and warranty lookup endpoints require two factors and apply POST rate limiting.
- Utility responses use private no-store caching and noindex metadata.
- Public Inertia props use presentation DTOs instead of raw Eloquent models.
- No production credentials or production database were used for QA.
