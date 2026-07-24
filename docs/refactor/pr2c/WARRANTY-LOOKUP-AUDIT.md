# Warranty Lookup Audit

## Domain availability

The repository has a real `warranties` table and `Warranty` model, linked optionally to `orders`. Existing public lookup uses `serial_number` only and returns the raw model.

## Required contract

Public lookup requires `serial_number` and the related order's customer phone. A warranty without an order is not publicly discoverable because the second identity factor cannot be verified. All misses use one generic message, requests are rate limited, response data is a bounded DTO, and responses use `private, no-store` plus `Pragma: no-cache`. The page is `noindex, nofollow`.
