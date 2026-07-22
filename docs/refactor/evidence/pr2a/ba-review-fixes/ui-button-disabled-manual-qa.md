# Disabled UiButton — Manual QA Evidence

**Date:** 2026-07-22
**Component:** UiButton.vue

## Changes Made

When `disabled` prop is `true` and `href` is present:

- Component renders `<span>` instead of `<a>` or Inertia `<Link>`
- No `href` attribute on the rendered element
- `role="link"` for semantic indication
- `aria-disabled="true"` for assistive technology
- `tabindex="-1"` removes from tab order
- `@click.capture`, `@keydown.enter.capture`, `@keydown.space.capture`
  all prevent default as safety nets
- Visual: `pointer-events-none opacity-55` classes applied

When `disabled` and no `href`:
- Native `<button disabled>` used (unchanged, already correct)

## Test Checklist

- [x] Mouse click on disabled link-button: no navigation
- [x] Enter key on disabled link-button: no navigation
- [x] Space key on disabled link-button: no navigation
- [x] Tab key does not focus disabled link-button
- [x] Enabled button with href: normal navigation works
- [x] Enabled external link: opens in new tab
- [x] tel: and mailto: links work when not disabled
