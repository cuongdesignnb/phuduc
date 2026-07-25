<?php

namespace App\Services\Admin;

use App\Models\User;

class AdminPageService
{
    public function __construct(private readonly AdminPermissionService $permissions) {}

    /**
     * @param  array<int, array<string, mixed>>  $breadcrumbs
     * @param  array<string, mixed>  $module
     * @return array<string, mixed>
     */
    public function envelope(User $user, string $type, string $title, array $breadcrumbs, array $module): array
    {
        return [
            'page' => [
                'type' => $type,
                'meta' => ['title' => $title],
                'admin' => [
                    'breadcrumbs' => $breadcrumbs,
                    'permissions' => $this->permissions->for($user),
                ],
                'module' => $module,
            ],
        ];
    }
}
