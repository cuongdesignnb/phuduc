# Stock Cancellation Audit

## Existing checkout behavior

`CheckoutService` locks products in ascending ID order, creates order-item snapshots and decrements each product stock inside the checkout transaction. The order starts in `pending`.

## Current admin behavior

The existing status endpoint only updates `orders.status`. It does not restore stock and has no history or idempotency guard.

## Target restoration rule

When a valid transition from `pending` or `processing` to `cancelled` commits, restore each resolvable product by the immutable order-item quantity exactly once. Lock order items and products in deterministic ascending ID order. A status history row is the audit marker and the version guard prevents a second concurrent cancellation from restoring stock again.

Missing or deleted products are recorded as safe unresolved lines and must not abort cancellation. Shipping, completed and already-cancelled orders never restore stock.
