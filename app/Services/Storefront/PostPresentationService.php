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
            'meta_title' => $post->meta_title,
            'meta_description' => $post->meta_description,
            'meta_keywords' => $post->meta_keywords,
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
            'gallery' => $post->relationLoaded('gallery')
                ? $post->gallery->map(fn ($gallery) => [
                    'id' => $gallery->id,
                    'url' => $this->mediaUrl->resolve($gallery->media?->file_path),
                    'alt_text' => $gallery->media?->alt_text ?: $post->title,
                ])->values()->all()
                : [],
            'updated_at' => $post->updated_at?->toIso8601String(),
        ];
    }
}
