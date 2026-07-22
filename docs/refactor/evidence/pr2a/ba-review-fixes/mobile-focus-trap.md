# Mobile Focus Trap — Manual QA Evidence

**Date:** 2026-07-22
**Component:** MobileNavigation.vue, StorefrontHeader.vue

## Changes Made

1. **Focus trap**: Tab/Shift+Tab cycling within drawer. Uses FOCUSABLE_SELECTOR
   to find interactive elements, filters hidden/disabled. Recalculates on each
   Tab press to handle dynamic accordion content.

2. **Focus restoration**: When drawer closes, focus returns to
   `mobileMenuTrigger` ref via nextTick. Guard prevents errors if trigger
   has unmounted (e.g., navigation to new page).

3. **Lifecycle-bound listeners**: keydown listener moved from module-level
   `document.addEventListener` to `onMounted`/`onBeforeUnmount`.

4. **Grandchild external link parity**: Added `:target` and `:rel` attributes
   to grandchild links matching the pattern used for top-level and child links.

5. **ARIA improvements**:
   - `id="mobile-navigation-dialog"` on aside
   - `aria-controls="mobile-navigation-dialog"` on trigger button
   - Child accordion button gets `aria-controls` referencing grandchild list
   - Grandchild `<ul>` gets stable `:id="mobile-submenu-${itemKey(child)}"`

## Test Checklist

- [x] Open drawer
- [x] Tab through all controls
- [x] Tab at last element wraps to first
- [x] Shift+Tab at first element wraps to last
- [x] Escape closes drawer
- [x] Focus returns to trigger button
- [x] Overlay click closes drawer
- [x] Accordion open/close does not close drawer
- [x] External grandchild opens new tab (target="_blank", rel="noopener noreferrer")
- [x] Body scroll restored on close
