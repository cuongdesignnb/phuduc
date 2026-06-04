<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeSectionItem extends Model
{
    protected $fillable = [
        'section_key',
        'title',
        'subtitle',
        'description',
        'image',
        'icon',
        'url',
        'metadata_json',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'metadata_json' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function section()
    {
        return $this->belongsTo(HomeSection::class, 'section_key', 'key');
    }
}
