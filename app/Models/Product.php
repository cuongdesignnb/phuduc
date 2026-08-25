<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'price', 'sku', 'stock', 'specifications', 'status',
        'youtube_url', 'meta_title', 'meta_description', 'meta_keywords',
    ];

    protected function casts(): array
    {
        return [
            'specifications' => 'array',
            'price' => 'decimal:2',
        ];
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class)->orderBy('sort_order');
    }

    public function cardImage()
    {
        return $this->hasOne(ProductImage::class)
            ->ofMany(
                ['sort_order' => 'min', 'id' => 'min'],
                fn ($query) => $query->where('is_360', false),
            );
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function approvedReviews()
    {
        return $this->hasMany(Review::class)->where('status', 'approved');
    }
}
