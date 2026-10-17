<?php

namespace App\Services\Admin\Content;

use App\Models\Post;
use App\Services\Admin\AdminPresentationService;
use App\Services\Admin\Media\MediaReferenceService;
use App\Services\Storefront\MediaUrlService;

class AdminPostPresentationService
{
    public function __construct(private readonly AdminPresentationService $presentation, private readonly MediaUrlService $mediaUrl, private readonly MediaReferenceService $mediaReferences) {}

    public function item(Post $post, ?int $featuredMediaId = null): array
    {
        return ['id' => $post->id, 'title' => $post->title, 'slug' => $post->slug, 'category' => $post->category ? ['id' => $post->category->id, 'name' => $post->category->name] : null, 'status' => $this->presentation->status($post->status), 'image_url' => $this->mediaUrl->resolve($post->featured_image), 'created_at_display' => $this->presentation->date($post->created_at), 'updated_at_display' => $this->presentation->date($post->updated_at), 'edit_url' => route('admin.posts.edit', $post), 'delete_url' => route('admin.posts.destroy', $post), 'featured_media_id' => $featuredMediaId];
    }

    public function edit(Post $post): array
    {
        return [
            'id' => $post->id,
            'title' => $post->title,
            'slug' => $post->slug,
            'summary' => $post->summary,
            'content' => $post->content,
            'status' => $post->status,
            'meta_title' => $post->meta_title,
            'meta_description' => $post->meta_description,
            'published_at' => $post->published_at?->toIso8601String(),
            'author_name' => $post->author?->name,
            'post_category_id' => $post->post_category_id,
            'featured_media_id' => $this->mediaReferences->idForPath($post->featured_image),
            'featured_image_url' => $this->mediaUrl->resolve($post->featured_image),
            'gallery' => $post->gallery->map(fn ($gallery) => [
                'id' => $gallery->id,
                'media_id' => $gallery->media_id,
                'url' => $this->mediaUrl->resolve($gallery->media?->file_path),
                'alt_text' => $gallery->media?->alt_text,
                'file_name' => $gallery->media?->file_name,
                'sort_order' => (int) $gallery->sort_order,
            ])->values()->all(),
            'version' => (string) optional($post->updated_at)->toISOString(),
        ];
    }
}
