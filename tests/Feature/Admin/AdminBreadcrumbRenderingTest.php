<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminBreadcrumbRenderingTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_contract_contains_the_breadcrumb_consumed_by_the_shell(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('page.admin.breadcrumbs.0.label', 'Tổng quan')
                ->where('page.admin.breadcrumbs.0.url', route('dashboard'))
            );
    }

    public function test_shell_wires_breadcrumb_component_and_authenticated_layout_slot(): void
    {
        $shell = file_get_contents(base_path('resources/js/Components/Admin/AdminShell.vue'));
        $layout = file_get_contents(base_path('resources/js/Layouts/AuthenticatedLayout.vue'));
        $breadcrumbs = file_get_contents(base_path('resources/js/Components/Admin/AdminBreadcrumbs.vue'));

        $this->assertStringContainsString("import AdminBreadcrumbs from './AdminBreadcrumbs.vue'", $shell);
        $this->assertStringContainsString(':items="breadcrumbs"', $shell);
        $this->assertStringContainsString('<slot name="breadcrumb">', $shell);
        $this->assertStringContainsString('#breadcrumb', $layout);
        $this->assertStringContainsString('index > 0', $breadcrumbs);
        $this->assertStringContainsString('aria-current="page"', $breadcrumbs);
    }
}
