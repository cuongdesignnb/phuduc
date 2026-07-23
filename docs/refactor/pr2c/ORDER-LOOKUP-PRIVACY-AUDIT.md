# Order Lookup Privacy Audit

## Baseline finding

The current POST query already accepts order number and phone, but validation is inline, failure is represented by a nullable raw model, and success returns raw order/item fields including internal IDs and timestamps. The page formats money and dates client-side.

## Required contract

Lookup stays POST-only and requires both order number and phone. It uses a rate limiter, generic failure text for all misses, a bounded presentation DTO without internal ID, intent, token, full email, address, or notes, backend-formatted totals/dates, and `private, no-store` plus `Pragma: no-cache`. The page is `noindex, nofollow`.
