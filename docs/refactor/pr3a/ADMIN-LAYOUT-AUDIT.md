# Admin layout audit

- Site name and logo are read from the shared canonical site configuration.
- Desktop sidebar supports expanded and collapsed modes.
- Mobile navigation is a dialog drawer with an overlay, Escape handling, initial focus, and focus return to the menu button.
- The shell provides a skip link, one main landmark, visible focus rings, and `aria-current="page"` on active links.
- Content uses responsive constraints and horizontal table scrolling to avoid page-level overflow.
- Existing admin pages retain the `header` slot contract.
