<?php

namespace Tests\Feature\Admin;

use App\Models\MediaLibrary;
use App\Models\Post;
use App\Models\PostMedia;
use App\Services\Admin\Media\MediaReferenceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PostGalleryContractTest extends TestCase
{
    use Pr3bTestHelpers, RefreshDatabase;

    public function test_post_album_is_saved_in_order_and_rendered_on_public_detail(): void
    {
        Storage::fake('public');
        $admin = $this->admin();
        $first = $this->media('first.jpg');
        $second = $this->media('second.jpg');

        $response = $this->actingAs($admin)->post(route('admin.posts.store'), [
            'title' => 'Bài viết có album',
            'slug' => '',
            'status' => 'published',
            'featured_media_id' => $first->id,
            'gallery_media_ids' => [$second->id, $first->id],
        ]);

        $response->assertRedirect();
        $post = Post::query()->where('slug', 'bai-viet-co-album')->firstOrFail();
        $this->assertSame([$second->id, $first->id], PostMedia::query()->where('post_id', $post->id)->orderBy('sort_order')->pluck('media_id')->all());
        $this->assertFalse(app(MediaReferenceService::class)->canDelete($second));

        $this->get(route('news.show', $post->slug))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('page.post.gallery.0.url', url('/storage/media/second.jpg'))
                ->where('page.post.gallery.1.url', url('/storage/media/first.jpg')));
    }

    private function media(string $fileName): MediaLibrary
    {
        $path = 'media/'.$fileName;
        Storage::disk('public')->put($path, 'image');

        return MediaLibrary::create([
            'file_name' => $fileName,
            'file_path' => $path,
            'mime_type' => 'image/jpeg',
            'size' => 5,
            'alt_text' => pathinfo($fileName, PATHINFO_FILENAME),
        ]);
    }
}
