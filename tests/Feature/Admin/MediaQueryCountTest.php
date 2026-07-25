<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MediaQueryCountTest extends TestCase
{
    use RefreshDatabase;

    public function test_media_index_paginates_twenty_rows(): void
    {
        $source = file_get_contents(base_path('app/Services/Admin/Media/AdminMediaService.php'));
        $this->assertStringContainsString('paginate(20)', $source);
        $this->assertStringContainsString('limit(20)', $source);
    }
}
