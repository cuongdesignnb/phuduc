# Privacy review

- Dashboard controller returns only the canonical service page payload.
- Recent orders omit public token, checkout intent, phone, email, shipping address, and notes.
- Recent reviews omit review content and customer contact fields.
- No raw Eloquent models are serialized by the dashboard DTOs.
- Temporary QA credentials and records were deleted before handoff.
