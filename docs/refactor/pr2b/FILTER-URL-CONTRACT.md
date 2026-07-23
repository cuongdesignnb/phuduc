# PR2B Filter URL Contract

## Product Index

Accepted query keys:

- `search`
- `min_price`
- `max_price`
- `sort`
- `page`

Validation:

- `search`: nullable string, max 100
- `min_price`: nullable numeric, min 0, max 1000000000000
- `max_price`: nullable numeric, min 0, max 1000000000000
- `sort`: nullable enum: `latest`, `price_asc`, `price_desc`, `name_asc`, `name_desc`
- `page`: nullable integer, min 1

Rules:

- Client cannot set `per_page`.
- Server pagination is fixed at 12.
- `min_price` must be less than or equal to `max_price` when both exist.
- Search must escape `%` and `_` before using `LIKE`.
- Empty filters are removed from generated URLs.
- Back/forward navigation must update filter form values from page props.

## News Index

Accepted query keys:

- `search`
- `category`
- `page`

Validation:

- `search`: nullable string, max 100
- `category`: nullable string, max 150
- `page`: nullable integer, min 1

Rules:

- Client cannot set `per_page`.
- Server pagination is fixed at 12.
- Search must escape `%` and `_`.
- Category counts include published posts only.
- Empty filters are removed from generated URLs.
