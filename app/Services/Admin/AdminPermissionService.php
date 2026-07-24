<?php

namespace App\Services\Admin;

use App\Models\User;

class AdminPermissionService
{
    /**
     * Keep the permission shape extensible while the application has one admin role.
     *
     * @return array<string, bool>
     */
    public function for(?User $user): array
    {
        $isAdmin = (bool) $user?->is_admin;

        return array_fill_keys([
            'admin.dashboard.view',
            'admin.products.view',
            'admin.media.view',
            'admin.orders.view',
            'admin.menus.view',
            'admin.home_content.view',
            'admin.posts.view',
            'admin.post_categories.view',
            'admin.reviews.view',
            'admin.warranties.view',
            'admin.settings.view',
        ], $isAdmin);
    }

    public function can(?User $user, string $permission): bool
    {
        return $this->for($user)[$permission] ?? false;
    }
}
