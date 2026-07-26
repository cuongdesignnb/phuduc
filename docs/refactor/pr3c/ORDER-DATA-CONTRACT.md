# Order Data Contract Audit

## Current source of truth

`orders` currently stores the customer snapshot, shipping address, notes, total, status, checkout intent and public token. `order_items` stores immutable product name, unit price, quantity and line total snapshots. The `Order` model exposes `items` and `warranties` relations.

## Public contract

Public order lookup may expose only order number, status label, created date, item snapshots and total. It must not expose customer email, full phone, shipping address, notes, checkout intent, public token, internal IDs or status history.

## Admin contract target

Admin pages will use explicit page envelopes and DTOs. Index rows must not eager-load all items. Detail responses may include item snapshots, customer operational fields and a bounded history timeline. Raw Eloquent models will not be passed to Inertia.

## Current gaps

- Admin controllers pass paginated models directly to Inertia.
- Status updates accept any enum value without transition or version checks.
- There is no order status history relation/table.
- There is no persisted order version for stale-write detection.
- Cancellation has no required reason or stock-restoration contract.
- Search clauses are not grouped before status filters.
