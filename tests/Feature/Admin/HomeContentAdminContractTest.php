<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeContentAdminContractTest extends TestCase
{
    use RefreshDatabase, Pr3bTestHelpers;

    public function test_home_content_exposes_registry_and_version_in_module(): void
    {
        $response = $this->actingAs($this->admin())->get(route('admin.home-content.index'));
        $this->assertAdminPage($response, 'admin_home_content_index');
        $response->assertInertia(fn ($page) => $page->has('page.module.registry')->has('page.module.sections')->has('page.module.version'));
    }
}
