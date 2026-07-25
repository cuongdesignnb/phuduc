<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminContentQueryCountTest extends TestCase
{
    use RefreshDatabase;

    public function test_pr3b_index_pages_stay_within_query_budget(): void
    {
        $admin = \App\Models\User::factory()->admin()->create();
        $routes = [
            'PRODUCT_INDEX' => route('admin.products.index'),
            'MEDIA_INDEX' => route('admin.media.index'),
            'POST_INDEX' => route('admin.posts.index'),
            'CATEGORY_INDEX' => route('admin.post-categories.index'),
            'MENU_INDEX' => route('admin.menus.index'),
            'HOME_CONTENT' => route('admin.home-content.index'),
            'SETTINGS' => route('admin.settings.index'),
        ];
        foreach ($routes as $label => $url) {
            DB::flushQueryLog();
            DB::enableQueryLog();
            $this->actingAs($admin)->get($url)->assertOk();
            $count = count(DB::getQueryLog());
            fwrite(STDOUT, "PR3B_{$label}={$count}\n");
            $this->assertLessThan(30, $count);
        }
    }
}
