<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostAdminContractTest extends TestCase
{
    use RefreshDatabase, Pr3bTestHelpers;

    public function test_post_index_and_create_use_canonical_page_contract(): void
    {
        $admin = $this->admin();
        $this->assertAdminPage($this->actingAs($admin)->get(route('admin.posts.index')), 'admin_posts_index');
        $this->assertAdminPage($this->actingAs($admin)->get(route('admin.posts.create')), 'admin_posts_edit');
    }
}
