# Settings Registry Audit

- Settings are discovered from the database and grouped by a key prefix.
- The client can submit `type`, so server-owned type/validation is not enforced.
- Unknown keys are accepted and created by `Setting::set`.
- `home.*` is rejected, but there is no server-owned registry for the remaining groups.
- Saves happen one row at a time and can leave partial updates.
- Image values accept arbitrary strings; font and color validation are not attached to a setting definition.
- `ThemeTokenService` is available for the font/color safety contract.
