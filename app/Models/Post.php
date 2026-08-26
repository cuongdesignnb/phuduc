<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = [
        'post_category_id', 'title', 'slug', 'summary', 'content', 'featured_image', 'status',
        'author_id', 'published_at', 'meta_title', 'meta_description',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Post $post): void {
            if ($post->status === 'published' && $post->published_at === null) {
                $post->published_at = now();
            }
        });

        static::updating(function (Post $post): void {
            if (
                $post->isDirty('status')
                && $post->status === 'published'
                && $post->getOriginal('status') !== 'published'
                && $post->published_at === null
            ) {
                $post->published_at = now();
            }
        });
    }

    public function category()
    {
        return $this->belongsTo(PostCategory::class, 'post_category_id');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function gallery()
    {
        return $this->hasMany(PostMedia::class)->orderBy('sort_order');
    }
}
