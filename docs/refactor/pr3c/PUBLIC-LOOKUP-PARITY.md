# Public Lookup Parity Audit

## Order lookup

The public order lookup requires normalized order number and customer phone, uses a generic failure message and presents a bounded DTO. The contract must remain two-factor and must not expose address, email, full phone, notes, tokens or history.

## Warranty lookup

The current lookup fetches by serial and requires a linked order phone. PR3C will normalize the serial, resolve the expected phone from the warranty snapshot first and the linked order second for legacy rows, then compare normalized phones before presenting any result. Manual warranties must be supported.

## Shared requirements

Both lookup paths remain rate limited, privacy-safe and generic on failure. Controllers will delegate lookup and presentation to services; no raw models or internal identifiers are returned to Inertia.
