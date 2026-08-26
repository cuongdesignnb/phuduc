<?php

namespace Tests\Feature\Admin;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostAuthorPublicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_publishing_an_unattributed_draft_assigns_the_current_admin_and_a_publication_time(): void
    {
        $admin = User::factory()->admin()->create();
        $post = Post::create(['title' => 'Bản nháp', 'slug' => 'ban-nhap', 'status' => 'draft']);

        $this->actingAs($admin)
            ->put(route('admin.posts.update', $post), $this->payload($post, ['status' => 'published']))
            ->assertRedirect(route('admin.posts.edit', $post));

        $post->refresh();

        $this->assertSame($admin->id, $post->author_id);
        $this->assertNotNull($post->published_at);
    }

    public function test_editing_an_existing_article_preserves_its_publication_time(): void
    {
        $admin = User::factory()->admin()->create();
        $publishedAt = now()->subDays(3)->startOfMinute();
        $post = Post::create([
            'title' => 'Bài đã đăng',
            'slug' => 'bai-da-dang',
            'status' => 'published',
            'author_id' => $admin->id,
            'published_at' => $publishedAt,
        ]);

        $this->actingAs($admin)
            ->put(route('admin.posts.update', $post), $this->payload($post, ['title' => 'Bài đã đăng được sửa']))
            ->assertRedirect(route('admin.posts.edit', $post));

        $this->assertTrue($publishedAt->equalTo($post->fresh()->published_at));
    }

    public function test_a_different_admin_cannot_overwrite_an_existing_post_author(): void
    {
        $originalAuthor = User::factory()->admin()->create();
        $editor = User::factory()->admin()->create();
        $post = Post::create([
            'title' => 'Bài đã có tác giả',
            'slug' => 'bai-da-co-tac-gia',
            'status' => 'published',
            'author_id' => $originalAuthor->id,
        ]);

        $this->actingAs($editor)
            ->put(route('admin.posts.update', $post), $this->payload($post, ['content' => '<p>Nội dung đã cập nhật.</p>']))
            ->assertRedirect(route('admin.posts.edit', $post));

        $this->assertSame($originalAuthor->id, $post->fresh()->author_id);
    }

    /** @param array<string, mixed> $overrides */
    private function payload(Post $post, array $overrides = []): array
    {
        return array_replace([
            'title' => $post->title,
            'slug' => $post->slug,
            'summary' => $post->summary,
            'content' => $post->content,
            'status' => $post->status,
            'version' => $post->updated_at->toISOString(),
        ], $overrides);
    }
}
