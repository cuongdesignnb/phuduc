<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiContentSchedule extends Model
{
    protected $fillable = [
        'user_id', 'post_id', 'category_id', 'topic', 'keywords', 'type', 'tone', 'length',
        'with_images', 'image_count', 'auto_publish', 'status', 'attempts', 'error_message',
        'scheduled_at', 'locked_at', 'started_at', 'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'keywords' => 'array',
            'with_images' => 'boolean',
            'auto_publish' => 'boolean',
            'scheduled_at' => 'datetime',
            'locked_at' => 'datetime',
            'started_at' => 'datetime',
            'processed_at' => 'datetime',
        ];
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(PostCategory::class, 'category_id');
    }

    public function claim(int $maxAttempts = 3): bool
    {
        $this->refresh();
        if ($this->status !== 'pending' || $this->attempts >= $maxAttempts) {
            return false;
        }

        $updated = static::query()
            ->whereKey($this->id)
            ->where('status', 'pending')
            ->where('attempts', '<', $maxAttempts)
            ->update([
                'status' => 'running',
                'attempts' => $this->attempts + 1,
                'locked_at' => now(),
                'started_at' => now(),
                'error_message' => null,
                'updated_at' => now(),
            ]);

        if ($updated !== 1) {
            return false;
        }

        $this->refresh();

        return true;
    }
}
