<?php

namespace Tests\Feature\Admin;

use App\Services\Admin\Content\AdminSettingRegistry;
use Tests\TestCase;

class SettingRegistryTest extends TestCase
{
    public function test_registry_contains_owned_font_keys_and_no_home_keys(): void
    {
        $keys = array_keys(AdminSettingRegistry::all());
        $this->assertContains('font.heading', $keys);
        $this->assertContains('font.body', $keys);
        $this->assertCount(0, array_filter($keys, fn ($key) => str_starts_with($key, 'home.')));
    }
}
