<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductQueryCountTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_index_uses_card_image_projection(): void
    {
        $source = file_get_contents(base_path('app/Services/Admin/Catalog/AdminProductService.php'));
        $this->assertStringContainsString("'cardImage' => fn (\$query) => \$query->select([", $source);
        $this->assertStringContainsString("'product_images.product_id'", $source);
        $this->assertStringNotContainsString("with('cardImage:id,product_id", $source);
        $this->assertStringNotContainsString("with('images')", $source);
    }
}
