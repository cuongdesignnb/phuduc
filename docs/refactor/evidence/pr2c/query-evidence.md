# PR2C Query Evidence

Date: 2026-07-23

- Cart presentation exposes numeric `unit_price`, `subtotal`, `total`, `item_count`, `quantity_count`, and `max_quantity` alongside display strings.
- Checkout success and lookup results use public order numbers/tokens and contain no raw phone, email, address, internal numeric ID, or intent token.
- Cart and checkout mutation query evidence remains bounded by the existing PR2C query-count tests.
- MySQL verification used the isolated database `phuduc_pr2c_test`; restored application data was not used for destructive test refreshes.
- Synthetic browser QA data was removed by exact identifiers after the run.
