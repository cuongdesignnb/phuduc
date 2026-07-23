# Cart Session Audit

## Baseline finding

The session cart is written by `CartController` as a map keyed by product id, but each value contains presentation fields (`name`, `price`, `slug`, `image`) in addition to quantity. The shared Inertia `cart` prop exposes this raw structure to every page.

## Required contract

The canonical stored value is only `[$productId => ['quantity' => int]]`. On every read, IDs and quantities are normalized, products are loaded in one query with `cardImage`, inactive/contact-price/missing products are removed, quantities are capped by current stock and 99, and the normalized session is persisted. Presentation data and totals are backend-owned.

## Security risks closed by PR2C

- No session price/name/image/stock trust.
- No stale product presentation.
- No N+1 image queries.
- Quantity mutations require active, priced, in-stock products and integer bounds.
- Remove is idempotent; clear is an explicit POST action.
