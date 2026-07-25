<?php

namespace Tests\Feature\Admin;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\PostCategory;
use App\Services\Admin\Content\AdminPostCategoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CategoryMenuReferenceGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_dto_exposes_menu_reference_count_and_delete_is_blocked(): void
    {
        $category = PostCategory::create(['name' => 'Tin tức', 'slug' => 'tin-tuc']);
        $menu = Menu::create(['name' => 'Header', 'location' => 'header']);
        MenuItem::create(['menu_id' => $menu->id, 'title' => 'Tin tức', 'model_type' => 'category', 'model_id' => $category->id, 'sort_order' => 0]);

        $page = app(AdminPostCategoryService::class)->index($this->admin());
        $this->assertSame(1, $page['page']['module']['items'][0]['menu_references_count']);
        $this->assertFalse($page['page']['module']['items'][0]['can_delete']);

        $this->expectException(ValidationException::class);
        app(AdminPostCategoryService::class)->destroy($category);
    }

    private function admin(): \App\Models\User
    {
        return \App\Models\User::factory()->admin()->create();
    }
}
