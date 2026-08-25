<?php

namespace App\Console\Commands;

use App\Jobs\ProcessAiContentSchedule;
use App\Models\AiContentSchedule;
use Illuminate\Console\Command;

class ProcessAiContentSchedules extends Command
{
    protected $signature = 'ai:process-schedules {--limit=10}';

    protected $description = 'Đưa các lịch sinh nội dung AI đến hạn vào queue.';

    public function handle(): int
    {
        $limit = min(max((int) $this->option('limit'), 1), 50);
        $ids = AiContentSchedule::query()
            ->where('status', 'pending')
            ->where('scheduled_at', '<=', now())
            ->where('attempts', '<', 3)
            ->orderBy('scheduled_at')
            ->limit($limit)
            ->pluck('id');

        foreach ($ids as $id) {
            ProcessAiContentSchedule::dispatch((int) $id);
        }

        $this->info("{$ids->count()} lịch AI đã được đưa vào queue.");

        return self::SUCCESS;
    }
}
