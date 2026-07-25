<?php

namespace Tests\Feature\Admin;

use App\Models\PostCategory;
use App\Services\Admin\Content\AdminPostCategoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PostCategoryHierarchyTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_cannot_become_its_own_descendant(): void
    {
        $parent = PostCategory::create(['name' => 'Parent', 'slug' => 'parent']);
        $child = PostCategory::create(['name' => 'Child', 'slug' => 'child', 'parent_id' => $parent->id]);
        $this->expectException(ValidationException::class);
        app(AdminPostCategoryService::class)->update($parent, ['name' => 'Parent', 'slug' => 'parent', 'parent_id' => $child->id]);
    }
}
