<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiGeneration extends Model
{
    protected $fillable = [
        'generation_id', 'user_id', 'kind', 'provider', 'model', 'status',
        'request_payload', 'result_payload', 'usage', 'warnings', 'error_message',
        'started_at', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'request_payload' => 'array',
            'result_payload' => 'array',
            'usage' => 'array',
            'warnings' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
