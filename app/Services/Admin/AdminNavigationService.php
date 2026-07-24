<?php

namespace App\Services\Admin;

use App\Models\User;

class AdminNavigationService
{
    public function __construct(private readonly AdminPermissionService $permissions) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        return [
            ['key' => 'dashboard', 'label' => 'Tổng quan', 'route' => 'dashboard', 'icon' => 'dashboard', 'active_patterns' => ['dashboard'], 'permission' => 'admin.dashboard.view'],
            ['key' => 'products', 'label' => 'Sản phẩm', 'route' => 'admin.products.index', 'icon' => 'cube', 'active_patterns' => ['admin.products.*'], 'permission' => 'admin.products.view'],
            ['key' => 'media', 'label' => 'Media', 'route' => 'admin.media.index', 'icon' => 'image', 'active_patterns' => ['admin.media.*'], 'permission' => 'admin.media.view'],
            ['key' => 'orders', 'label' => 'Đơn hàng', 'route' => 'admin.orders.index', 'icon' => 'cart', 'active_patterns' => ['admin.orders.*'], 'permission' => 'admin.orders.view'],
            ['key' => 'menus', 'label' => 'Menu', 'route' => 'admin.menus.index', 'icon' => 'menu', 'active_patterns' => ['admin.menus.*'], 'permission' => 'admin.menus.view'],
            ['key' => 'home_content', 'label' => 'Nội dung trang chủ', 'route' => 'admin.home-content.index', 'icon' => 'home', 'active_patterns' => ['admin.home-content.*'], 'permission' => 'admin.home_content.view'],
            ['key' => 'posts', 'label' => 'Bài viết', 'route' => 'admin.posts.index', 'icon' => 'document', 'active_patterns' => ['admin.posts.*'], 'permission' => 'admin.posts.view'],
            ['key' => 'post_categories', 'label' => 'Danh mục tin', 'route' => 'admin.post-categories.index', 'icon' => 'folder', 'active_patterns' => ['admin.post-categories.*'], 'permission' => 'admin.post_categories.view'],
            ['key' => 'reviews', 'label' => 'Đánh giá', 'route' => 'admin.reviews.index', 'icon' => 'star', 'active_patterns' => ['admin.reviews.*'], 'permission' => 'admin.reviews.view'],
            ['key' => 'warranties', 'label' => 'Bảo hành', 'route' => 'admin.warranties.index', 'icon' => 'shield', 'active_patterns' => ['admin.warranties.*'], 'permission' => 'admin.warranties.view'],
            ['key' => 'settings', 'label' => 'Cài đặt', 'route' => 'admin.settings.index', 'icon' => 'cog', 'active_patterns' => ['admin.settings.*'], 'permission' => 'admin.settings.view'],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function for(?User $user): array
    {
        $permissions = $this->permissions->for($user);

        return array_values(array_filter(
            $this->all(),
            fn (array $item): bool => $permissions[$item['permission']] ?? false,
        ));
    }
}
