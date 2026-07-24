# Manual QA

- Login: local synthetic admin account authenticated successfully; credentials were removed after QA.
- Dashboard empty: zero-state cards, charts, recent orders, recent reviews, and products rendered.
- Dashboard populated: temporary PR3A QA records rendered in orders and products, then removed.
- Viewports: 360x800, 390x844, 768x1024, 1024x768, 1280x900, 1440x1000, 1920x1080.
- Responsive result: no horizontal overflow at any viewport; one H1 and one main landmark at every viewport.
- Desktop sidebar: expanded and collapsed states verified.
- Mobile drawer: open, overlay close, Escape close, initial focus, and focus return verified.
- Account dropdown: opened successfully at desktop width.
- Browser console: no error or warning entries after QA.
- Semantic Admin tokens: no direct carbon/volt classes in PR3A scope.
- Breadcrumb: visible on Dashboard, no leading separator, final item is current page.
- Confirm dialog: initial focus, Tab and Shift+Tab containment, Escape, focus return, and processing guard verified.
- Pagination fixture: disabled previous item, active page item, plain-text labels, and no hash navigation verified by contract tests.
- Mobile drawer: Tab and Shift+Tab containment plus body scroll lock verified by shared composable contract and browser QA.
