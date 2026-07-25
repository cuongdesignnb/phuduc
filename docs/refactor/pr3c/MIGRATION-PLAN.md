# PR3C Migration Plan

## Order operations

Add an order version column with a safe default and an `order_status_histories` table containing order ID, nullable actor ID, from/to status, optional reason and timestamps. Add indexes for order/time and target status. Foreign keys must cascade history with its order and null actor references on user deletion.

## Warranty operations

Add nullable order-item and customer snapshot fields, source metadata needed to distinguish order/manual records, a version and nullable void reason. Add lookup-oriented indexes without rewriting legacy rows. Existing serial values are preserved; application normalization provides case-insensitive behavior across SQLite and MySQL.

## Lifecycle checks

Run migrations, rollback and migrate again on isolated SQLite and MySQL databases. Do not use `migrate:fresh`, `db:wipe`, `migrate:reset`, production credentials or restored production data.

## Rollback principle

Down migrations remove only PR3C structures and columns in reverse dependency order. No migration deletes business rows as part of normal PR3C validation.
