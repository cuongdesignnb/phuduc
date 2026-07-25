# Menu Integrity Audit

- Menu edit loads recursive Eloquent relations and returns them directly to Vue.
- `saveItems` deletes all existing menu items and recreates the tree.
- Client-provided `model_type` is unrestricted.
- Menu ownership is not checked for nested item IDs because no item IDs are accepted by the current save contract.
- There is no transaction, version/fingerprint, maximum depth/node guard, stable client key, or stale-save rejection.
- URL validation is only a nullable string rule.
- The storefront navigation reads `header` and `footer`; valid target types must be server-owned.
