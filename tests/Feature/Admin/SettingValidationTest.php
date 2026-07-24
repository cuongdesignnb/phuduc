<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingValidationTest extends TestCase
{
    use RefreshDatabase, Pr3bTestHelpers;

    public function test_unregistered_setting_is_rejected(): void
    {
        $this->actingAs($this->admin())->postJson(route('admin.settings.save'), ['settings' => [['key' => 'home.hero_title', 'value' => 'x']]])->assertUnprocessable()->assertJsonValidationErrors('settings');
    }
}
