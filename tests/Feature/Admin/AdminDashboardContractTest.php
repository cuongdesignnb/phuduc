<?php

namespace Tests\Feature\Admin;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminDashboardContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_returns_the_canonical_admin_page_contract(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->where('page.type', 'admin_dashboard')
                ->where('page.meta.title', 'Tổng quan')
                ->has('page.admin.navigation', 11)
                ->has('page.admin.breadcrumbs')
                ->has('page.admin.permissions')
                ->has('page.dashboard.summary', 9)
                ->has('page.dashboard.orders_by_status')
                ->count('page.dashboard.monthly_revenue', 6)
                ->has('page.dashboard.recent_orders')
                ->has('page.dashboard.recent_reviews')
                ->has('page.dashboard.top_products')
            );
    }

    public function test_populated_dashboard_exposes_server_formatted_values(): void
    {
        $admin = User::factory()->admin()->create();
        Product::create(['name' => 'Contract product', 'slug' => 'contract-product', 'price' => 1250000, 'stock' => 4, 'status' => 'active']);

        $this->actingAs($admin)->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('page.dashboard.top_products.0.price.value', 1250000)
                ->where('page.dashboard.top_products.0.price.display', '1.250.000 ₫')
                ->where('page.dashboard.top_products.0.status_label', 'Đang hoạt động')
            );
    }
}
