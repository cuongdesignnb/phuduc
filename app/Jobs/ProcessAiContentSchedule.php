<?php

namespace App\Jobs;

use App\Models\AiContentSchedule;
use App\Services\Admin\Ai\AiContentGenerationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessAiContentSchedule implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public int $tries = 3;

    public function __construct(public readonly int $scheduleId) {}

    public function backoff(): array
    {
        return [60, 300, 900];
    }

    public function handle(AiContentGenerationService $generator): void
    {
        $schedule = AiContentSchedule::query()->find($this->scheduleId);
        if (! $schedule || ! $schedule->claim()) {
            return;
        }

        try {
            $result = $generator->generate([
                'type' => $schedule->type,
                'topic' => $schedule->topic,
                'keywords' => $schedule->keywords,
                'tone' => $schedule->tone,
                'length' => $schedule->length,
                'full_article' => true,
                'category_id' => $schedule->category_id,
                'with_images' => $schedule->with_images,
                'image_count' => $schedule->image_count,
            ], $schedule->user);
            $generation = \App\Models\AiGeneration::query()->where('generation_id', $result['generation_id'])->firstOrFail();
            $post = $generator->saveArticle($generation, $schedule->user, $schedule->auto_publish);
            $schedule->update([
                'status' => 'completed',
                'post_id' => $post->id,
                'processed_at' => now(),
                'locked_at' => null,
            ]);
        } catch (\Throwable $exception) {
            $schedule->update([
                'status' => $schedule->attempts >= 3 ? 'failed' : 'pending',
                'error_message' => mb_substr($exception->getMessage(), 0, 1000),
                'locked_at' => null,
            ]);
            throw $exception;
        }
    }
}
