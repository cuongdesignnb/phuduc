<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductAdminContractTest extends TestCase
{
    use RefreshDatabase, Pr3bTestHelpers;

    public function test_product_index_and_create_use_canonical_page_contract(): void
    {
        $admin = $this->admin();
        $this->assertAdminPage($this->actingAs($admin)->get(route('admin.products.index')), 'admin_products_index');
        $this->assertAdminPage($this->actingAs($admin)->get(route('admin.products.create')), 'admin_products_edit');
    }
}
