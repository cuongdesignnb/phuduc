<?php

namespace Tests\Feature\Admin;

use App\Models\PostCategory;
use App\Services\Admin\Content\AdminPostCategoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PostCategoryDeleteGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_with_children_cannot_be_deleted(): void
    {
        $parent = PostCategory::create(['name' => 'Parent', 'slug' => 'parent']);
        PostCategory::create(['name' => 'Child', 'slug' => 'child', 'parent_id' => $parent->id]);
        $this->expectException(ValidationException::class);
        app(AdminPostCategoryService::class)->destroy($parent);
    }
}
