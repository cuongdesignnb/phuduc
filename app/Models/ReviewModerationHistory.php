<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewModerationHistory extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'review_id', 'review_reference', 'actor_id', 'action', 'from_status', 'to_status', 'reason', 'created_at',
    ];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    public function review()
    {
        return $this->belongsTo(Review::class);
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
