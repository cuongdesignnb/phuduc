<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeContentLookupQueryTest extends TestCase
{
    use RefreshDatabase, Pr3bTestHelpers;

    public function test_lookup_endpoints_return_bounded_dtos(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->getJson(route('admin.home-content.products'))->assertOk()->assertJsonStructure(['data']);
        $this->actingAs($admin)->getJson(route('admin.home-content.posts'))->assertOk()->assertJsonStructure(['data']);
        $source = file_get_contents(base_path('app/Services/Admin/Content/AdminHomeContentService.php'));
        $this->assertSame(2, substr_count($source, 'limit(20)'));
    }
}
