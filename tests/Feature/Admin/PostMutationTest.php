<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostMutationTest extends TestCase
{
    use RefreshDatabase, Pr3bTestHelpers;

    public function test_post_creation_generates_unique_slug(): void
    {
        $this->actingAs($this->admin())->post(route('admin.posts.store'), ['title' => 'News story', 'slug' => '', 'status' => 'draft'])->assertRedirect();
        $this->assertDatabaseHas('posts', ['slug' => 'news-story']);
    }
}
