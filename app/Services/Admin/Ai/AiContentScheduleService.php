<?php

namespace App\Services\Admin\Ai;

use App\Models\AiContentSchedule;
use App\Models\User;

final class AiContentScheduleService
{
    /** @return list<array<string, mixed>> */
    public function all(): array
    {
        return AiContentSchedule::query()->with('category:id,name')->latest('scheduled_at')->limit(50)->get()->map(fn (AiContentSchedule $schedule) => [
            'id' => $schedule->id,
            'topic' => $schedule->topic,
            'keywords' => $schedule->keywords ?? [],
            'type' => $schedule->type,
            'tone' => $schedule->tone,
            'length' => $schedule->length,
            'with_images' => $schedule->with_images,
            'image_count' => $schedule->image_count,
            'auto_publish' => $schedule->auto_publish,
            'category' => $schedule->category?->name,
            'status' => $schedule->status,
            'attempts' => $schedule->attempts,
            'error_message' => $schedule->error_message,
            'scheduled_at' => optional($schedule->scheduled_at)->format('Y-m-d H:i'),
            'post_id' => $schedule->post_id,
        ])->all();
    }

    public function create(array $data, User $user): AiContentSchedule
    {
        return AiContentSchedule::create([
            ...$data,
            'user_id' => $user->id,
            'keywords' => array_values(array_filter(array_map('trim', (array) ($data['keywords'] ?? [])))),
            'status' => 'pending',
        ]);
    }
}
