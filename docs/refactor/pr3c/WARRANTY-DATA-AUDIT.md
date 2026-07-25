# Warranty Data Audit

## Current schema

`warranties` stores an optional order ID, serial number, product-name snapshot, activation date, expiration date and stored status (`active`, `expired`, `voided`). Serial is currently database-unique but input normalization and case-insensitive parity are not enforced by the application.

## Current gaps

- The create/edit screen loads a fixed first 100 orders.
- There is no order-item ownership check or explicit order/manual mode.
- Customer snapshot fields are absent, so public lookup cannot prefer a warranty snapshot.
- Status is edited directly without version or void reason.
- Warranty rows are hard-deleted by the resource controller.
- Public lookup requires a linked order and cannot resolve manual or legacy fallback cases.
- Effective status does not account for activation/expiration dates.
- Admin responses expose raw warranty/order models.

## Target data contract

PR3C adds the minimal nullable snapshot and source fields needed to preserve legacy rows, an order-item link where available, a version and void reason. Serial input is trim/uppercase normalized before lookup and uniqueness checks. Existing rows are never hard-deleted.
