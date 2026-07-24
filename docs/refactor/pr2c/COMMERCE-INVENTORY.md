# Commerce Inventory

## Routes

`routes/web.php` defines GET/POST/PATCH/DELETE cart and checkout routes, a numeric model-bound success route, POST order lookup, and POST warranty lookup. Cart mutations are POST/PATCH/DELETE; there is no explicit clear-cart route.

## Existing backend

- `CartController` reads and writes `session('cart')`, validates existence only, and stores `product_id`, `name`, `price`, `slug`, `image`, and `quantity`.
- `CheckoutController` reads session cart entries, calculates totals from `item['price']`, creates the order in a controller transaction, does not lock products or decrement stock, and clears the cart after creation.
- `OrderLookupController` eager-loads items but renders the raw `Order` model.
- `WarrantyLookupController` finds a warranty by `serial_number` only and renders the raw `Warranty` model.

## Existing models/schema

`Product` has `status`, nullable `price`, `stock`, and `cardImage`. `Order` has order number, customer identity, shipping data, total, status, and notes. `OrderItem` has product snapshot fields. `Warranty` has serial, product name, dates, status, and nullable order relation. Orders have no checkout idempotency or opaque public token columns in the base schema.

## Existing frontend

Cart, checkout, success, and order lookup calculate money or dates in Vue. Cart uses direct `/storage/` paths. Utility pages do not provide page-specific noindex/no-store contracts. `GuestPageLayout` derives its count from the shared cart prop, which currently exposes the raw session schema.
