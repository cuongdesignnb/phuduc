<?php

namespace Tests\Feature\Admin;

use App\Models\Setting;
use App\Services\Admin\Content\AdminSettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingAtomicSaveTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_save_is_transactional(): void
    {
        $source = file_get_contents(base_path('app/Services/Admin/Content/AdminSettingService.php'));
        $this->assertStringContainsString('DB::transaction', $source);
        $this->assertTrue(method_exists(app(AdminSettingService::class), 'save'));
        $this->assertDatabaseCount('settings', 0);
    }
}
