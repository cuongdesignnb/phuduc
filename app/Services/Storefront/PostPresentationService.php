<?php

namespace App\Services\Storefront;

use App\Models\Post;

class PostPresentationService
{
    public function __construct(private readonly MediaUrlService $mediaUrl) {}

    /**
     * @return array<string, mixed>
     */
    public function card(Post $post): array
    {
        return [
            'id' => $post->id,
            'title' => $post->title,
            'slug' => $post->slug,
            'summary' => $post->summary,
            'image_url' => $this->mediaUrl->resolve($post->featured_image),
            'category' => $post->category ? [
                'name' => $post->category->name,
                'slug' => $post->category->slug,
            ] : null,
            'published_at' => $post->created_at?->toIso8601String(),
            'published_at_display' => $post->created_at?->format('d/m/Y') ?? '',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function detail(Post $post): array
    {
        return [
            ...$this->card($post),
            'content_html' => $post->content,
            'updated_at' => $post->updated_at?->toIso8601String(),
        ];
    }
}
