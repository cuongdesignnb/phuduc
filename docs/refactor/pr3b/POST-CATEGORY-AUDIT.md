# Post and Category Audit

- Post list/edit controllers return raw paginator/model data and perform validation and slug generation inline.
- Post featured image accepts an arbitrary string instead of a stable Media reference.
- Post delete has no Home Content or Menu Item reference guard.
- Category index recursively eager-loads `allChildren`; edit parent options exclude only the current category, not descendants.
- Category parent self-reference and descendant-cycle writes are currently possible.
- Category deletion relies on the database cascade and does not guard children or posts.
