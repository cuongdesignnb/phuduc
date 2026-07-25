<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeContentConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_content_uses_fingerprint_guard(): void
    {
        $source = file_get_contents(base_path('app/Services/Admin/Content/AdminHomeContentService.php'));
        $this->assertStringContainsString('assertFingerprint', $source);
        $this->assertStringContainsString('DB::transaction', $source);
    }
}
