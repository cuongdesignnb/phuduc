<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;

class AdminSharedComponentSourceTest extends TestCase
{
    public function test_pr3a_shared_components_use_semantic_tokens_and_safe_rendering(): void
    {
        $files = glob(base_path('resources/js/Components/Admin/*.vue'));
        $source = implode("\n", array_map('file_get_contents', $files));

        $this->assertStringNotContainsString('carbon-', $source);
        $this->assertStringNotContainsString('volt-', $source);
        $this->assertStringNotContainsString('v-html', $source);
        $this->assertStringNotContainsString('href="#"', $source);
        $this->assertStringContainsString('text-admin-content', $source);
        $this->assertStringContainsString('bg-admin-surface', $source);
    }
}
