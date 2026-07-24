<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuUrlValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_target_registry_is_the_single_model_type_allowlist(): void
    {
        $source = file_get_contents(base_path('app/Services/Admin/Content/MenuTargetRegistry.php'));
        $this->assertStringContainsString("'product'", $source);
        $this->assertStringContainsString("'post'", $source);
        $this->assertStringContainsString("'category'", $source);
    }
}
