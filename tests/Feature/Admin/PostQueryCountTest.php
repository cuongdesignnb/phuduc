<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostQueryCountTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_index_eager_loads_only_category_projection(): void
    {
        $source = file_get_contents(base_path('app/Services/Admin/Content/AdminPostService.php'));
        $this->assertStringContainsString("with('category:id,name')", $source);
        $this->assertStringContainsString('paginate(15)', $source);
    }
}
