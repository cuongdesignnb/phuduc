# Checkout Security Audit

## Baseline finding

The controller calculates the order total from `item['price']` and snapshots `item['name']` and `item['price']` from session. It does not read products for checkout, lock rows, enforce stock, or provide an idempotency key.

## Required contract

Checkout resolves the canonical cart, validates customer input through a Form Request, sorts product IDs, and inside a short transaction uses `lockForUpdate()`. It rechecks active status, integer stock, and positive current price, calculates integer VND totals from the database, writes order-item snapshots, decrements stock, and clears the cart only after commit.

GET creates an opaque checkout intent in session. POST requires that intent and stores it with a unique opaque public token. Repeated submissions return the same order and do not decrement stock twice. Failure preserves the cart.
