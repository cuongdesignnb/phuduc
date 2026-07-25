# Home Content Audit

- `HomeContentController` already uses `HomeSectionRegistry` and an atomic transaction for section saves.
- The index still returns broad product/post collections and passes image paths rather than a dedicated picker contract.
- Manual product and post IDs are validated for existence but not scoped to the allowed active/public set.
- There is no page fingerprint/version check to prevent stale tab overwrites.
- Item ownership is checked, but update and delete policy need explicit service tests and a canonical DTO.
- Media fields in section config/item data accept arbitrary strings.
