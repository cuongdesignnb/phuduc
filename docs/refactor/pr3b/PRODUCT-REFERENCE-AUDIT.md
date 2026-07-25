# Product Reference Audit

- Product references: `order_items.product_id`, `reviews.product_id`, manual Home Content product IDs, and Menu Items with an allowed product target.
- Product images use `product_images.image_path`; there is no ownership column and Media attach currently stores the Media path directly.
- Product delete currently hard-deletes without checking historical or content references.
- Product list eager-loads all images instead of only `cardImage`.
- Product price is decimal-cast and formatted in Vue; the target is integer VND plus server display value.
- Product specifications have no bounded row/key/value or duplicate-key validation.
- Slug generation does not guarantee uniqueness when the generated slug collides.
- Product update has no submitted version/updated-at concurrency guard.
- Image reorder does not reject cross-product IDs atomically.
