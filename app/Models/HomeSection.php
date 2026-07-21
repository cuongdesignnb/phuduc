<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeSection extends Model
{
    protected $fillable = [
        'key',
        'type',
        'title',
        'subtitle',
        'description',
        'variant',
        'is_enabled',
        'sort_order',
        'settings_json',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'settings_json' => 'array',
        ];
    }

    public function items()
    {
        return $this->hasMany(HomeSectionItem::class, 'home_section_id')
            ->orderBy('sort_order');
    }

    public function activeItems()
    {
        return $this->items()->where('is_active', true);
    }
}
