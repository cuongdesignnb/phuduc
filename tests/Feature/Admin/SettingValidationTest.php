<?php

namespace Tests\Feature\Admin;

use App\Services\Admin\Content\AdminSettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingValidationTest extends TestCase
{
    use Pr3bTestHelpers, RefreshDatabase;

    public function test_unregistered_setting_is_rejected(): void
    {
        $admin = $this->admin();
        $version = app(AdminSettingService::class)->page($admin)['page']['module']['version'];
        $this->actingAs($admin)->postJson(route('admin.settings.save'), ['version' => $version, 'settings' => [['key' => 'home.hero_title', 'value' => 'x']]])->assertUnprocessable()->assertJsonValidationErrors('settings');
    }
}
