# PR3B Page Contracts

Every PR3B page uses one Inertia prop:

```php
[
    'page' => [
        'type' => 'admin_module_name',
        'meta' => ['title' => '...'],
        'admin' => ['breadcrumbs' => [...], 'permissions' => [...]],
        'module' => [...],
    ],
]
```

Lists use DTO rows, server pagination, normalized filters, and action URLs. Edit pages use DTO forms, server-owned options, a version/fingerprint, and field error metadata. No raw Eloquent model or paginator crosses the Inertia boundary.
