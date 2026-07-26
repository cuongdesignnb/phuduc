# Review Moderation Audit

## Current source of truth

`reviews` stores product ID, customer contact snapshot, content, rating and one of `pending`, `approved` or `rejected`. Storefront product pages already use approved reviews for public presentation.

## Current gaps

- Guest submission validates inline in the controller.
- The review route is not rate limited.
- Inactive products are not rejected by a server-side active-product guard.
- Content is not explicitly normalized as trimmed plain text.
- Admin status updates have no version guard or transition service.
- Admin delete directly hard-deletes every review, including approved rows.
- Admin responses pass raw models to Inertia.

## Target rules

Guest submissions are always created as pending. Moderation is server-owned and version guarded. Approved reviews cannot be deleted through the admin delete policy; rejected reviews may be removed. Storefront responses contain only approved reviews and explicit review DTO fields.
