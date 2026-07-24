<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuAdminContractTest extends TestCase
{
    use RefreshDatabase, Pr3bTestHelpers;

    public function test_menu_index_and_create_use_canonical_page_contract(): void
    {
        $admin = $this->admin();
        $this->assertAdminPage($this->actingAs($admin)->get(route('admin.menus.index')), 'admin_menus_index');
        $this->assertAdminPage($this->actingAs($admin)->get(route('admin.menus.create')), 'admin_menus_edit');
    }
}
