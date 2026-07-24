<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Services\Admin\AdminNavigationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminNavigationContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_navigation_is_single_source_and_contains_the_full_admin_module_set(): void
    {
        $items = app(AdminNavigationService::class)->for(User::factory()->admin()->create());
        $keys = array_column($items, 'key');

        $this->assertCount(11, $items);
        $this->assertSame($keys, array_values(array_unique($keys)));
        $this->assertContains('media', $keys);
        $this->assertSame('admin.media.index', $items[array_search('media', $keys, true)]['route']);
        $this->assertNotSame('', trim((string) file_get_contents(base_path('app/Services/Admin/AdminNavigationService.php'))));
    }
}
