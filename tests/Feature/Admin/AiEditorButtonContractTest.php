<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;

class AiEditorButtonContractTest extends TestCase
{
    public function test_product_and_post_editors_expose_both_ai_actions(): void
    {
        $postEditor = file_get_contents(base_path('resources/js/Pages/Admin/Post/Edit.vue'));
        $productEditor = file_get_contents(base_path('resources/js/Pages/Admin/Product/Edit.vue'));

        $this->assertStringContainsString("route('admin.ai.content.generate')", $postEditor);
        $this->assertStringContainsString('Sinh bài viết + Meta', $postEditor);
        $this->assertStringContainsString('Sinh bài viết kèm ảnh + thumbnail', $postEditor);
        $this->assertStringContainsString('with_images: withImages', $postEditor);
        $this->assertStringContainsString("route('admin.ai.products.seo'", $productEditor);
        $this->assertStringContainsString('Sinh mô tả + Meta', $productEditor);
        $this->assertStringContainsString('Sinh mô tả kèm ảnh', $productEditor);
        $this->assertStringContainsString('with_images: withImages', $productEditor);
    }
}
