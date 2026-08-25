<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = [
        'post_category_id', 'title', 'slug', 'summary', 'content', 'featured_image', 'status',
        'meta_title', 'meta_description', 'meta_keywords',
    ];

    public function category()
    {
        return $this->belongsTo(PostCategory::class, 'post_category_id');
    }

    public function gallery()
    {
        return $this->hasMany(PostMedia::class)->orderBy('sort_order');
    }
}
