# Media and Storage Audit

- `GET admin/media` currently returns `response()->json($medias)`.
- Upload accepts one arbitrary file and stores the client filename as metadata.
- MIME allowlist, total-file count, total-request size, and generated stable names are missing.
- `ProcessMediaUpload` changes `media_libraries.file_path` after the response and deletes the old file.
- Media deletion deletes the physical file before the database row and has no reference lookup.
- References currently exist in `ProductImage.image_path`, `Post.featured_image`, Setting image values, Home section config, and Home section item images.
- `MediaUrlService` is the canonical URL resolver and must be used by DTOs and pages.
- Existing `MediaBox` uses direct `/storage/` URLs and raw file path payloads.

## Target

Stable media path at row creation, reference-aware deletion, after-commit physical deletion, and a DTO-only picker endpoint.
