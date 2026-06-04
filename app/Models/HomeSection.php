<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeSection extends Model
{
    protected $fillable = [
        'key',
        'title',
        'subtitle',
        'description',
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
        return $this->hasMany(HomeSectionItem::class, 'section_key', 'key')
            ->orderBy('sort_order');
    }

    public function activeItems()
    {
        return $this->items()->where('is_active', true);
    }
}
