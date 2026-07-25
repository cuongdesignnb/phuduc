<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;

class ProductRichEditorContractTest extends TestCase
{
    public function test_product_description_uses_the_shared_rich_editor_and_image_only_picker(): void
    {
        $source = file_get_contents(base_path('resources/js/Pages/Admin/Product/Edit.vue'));
        $editor = file_get_contents(base_path('resources/js/Components/AdvancedTextEditor.vue'));

        $this->assertStringContainsString('import AdvancedTextEditor', $source);
        $this->assertStringContainsString('<AdvancedTextEditor id="product-description"', $source);
        $this->assertStringContainsString('media-type="image"', $editor);
    }
}
