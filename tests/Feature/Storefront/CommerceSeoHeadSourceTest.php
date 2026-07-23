<?php

namespace Tests\Feature\Storefront;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommerceSeoHeadSourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_pr2c_pages_use_seo_head_with_the_page_seo_contract(): void
    {
        foreach (['Cart', 'Checkout', 'CheckoutSuccess', 'OrderLookup', 'WarrantyLookup'] as $page) {
            $source = file_get_contents(resource_path("js/Pages/Guest/{$page}.vue"));

            $this->assertStringContainsString("import SeoHead from '@/Components/SeoHead.vue';", $source);
            $this->assertStringContainsString('<SeoHead v-bind="page.seo" />', $source);
            $this->assertStringNotContainsString('<Head v-bind="seo" />', $source);
        }
    }

    public function test_seo_component_renders_noindex_and_canonical_head_tags(): void
    {
        $source = file_get_contents(resource_path('js/Components/SeoHead.vue'));

        $this->assertStringContainsString('name="robots"', $source);
        $this->assertStringContainsString('rel="canonical"', $source);
    }
}
