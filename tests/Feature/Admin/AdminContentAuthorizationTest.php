<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminContentAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_access_pr3b_modules(): void
    {
        $user = \App\Models\User::factory()->create();
        foreach ([route('admin.products.index'), route('admin.media.index'), route('admin.posts.index'), route('admin.post-categories.index'), route('admin.menus.index'), route('admin.home-content.index'), route('admin.settings.index')] as $url) {
            $this->actingAs($user)->get($url)->assertForbidden();
        }
    }
}
