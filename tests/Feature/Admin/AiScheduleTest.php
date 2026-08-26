<?php

namespace Tests\Feature\Admin;

use App\Models\AiContentSchedule;
use App\Jobs\ProcessAiContentSchedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiScheduleTest extends TestCase
{
    use RefreshDatabase;

    public function test_schedule_claim_is_atomic_and_bounded(): void
    {
        $schedule = AiContentSchedule::create([
            'topic' => 'Chủ đề định kỳ', 'keywords' => ['xe điện'], 'scheduled_at' => now(),
            'type' => 'article', 'tone' => 'professional', 'length' => 'medium',
        ]);

        $this->assertTrue($schedule->claim());
        $this->assertFalse($schedule->fresh()->claim());
        $this->assertSame(1, $schedule->fresh()->attempts);
    }

    public function test_schedule_is_created_from_admin_page(): void
    {
        $admin = User::factory()->admin()->create();
        $response = $this->actingAs($admin)->post(route('admin.ai.schedules.store'), [
            'topic' => 'Xu hướng xe điện', 'keywords' => "xe điện\nxe chở hàng", 'type' => 'article',
            'tone' => 'professional', 'length' => 'medium', 'with_images' => false,
            'image_count' => 1, 'auto_publish' => false, 'scheduled_at' => now()->addHour()->format('Y-m-d H:i'),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('ai_content_schedules', ['topic' => 'Xu hướng xe điện', 'status' => 'pending']);
    }

    public function test_due_schedule_job_creates_a_draft_without_auto_publish(): void
    {
        $admin = User::factory()->admin()->create();
        config([
            'services.ai.content.api_key' => 'content-test-key',
            'services.ai.content.base_url' => 'https://ai.test/v1',
            'services.ai.content.model' => 'content-test-model',
        ]);
        Http::fake([
            'https://ai.test/*' => Http::response(['output_text' => json_encode([
                'title' => 'Bài từ lịch AI', 'excerpt' => 'Tóm tắt', 'content' => '<p>Nội dung từ lịch.</p>',
                'meta_title' => 'Bài từ lịch AI', 'meta_desc' => 'Mô tả',
            ], JSON_UNESCAPED_UNICODE)], 200),
        ]);
        $schedule = AiContentSchedule::create([
            'user_id' => $admin->id, 'topic' => 'Bài từ lịch AI', 'keywords' => ['lịch AI'], 'scheduled_at' => now(),
            'type' => 'article', 'tone' => 'professional', 'length' => 'short', 'auto_publish' => false,
        ]);

        (new ProcessAiContentSchedule($schedule->id))->handle(app(\App\Services\Admin\Ai\AiContentGenerationService::class));

        $this->assertDatabaseHas('ai_content_schedules', ['id' => $schedule->id, 'status' => 'completed']);
        $this->assertDatabaseHas('posts', ['title' => 'Bài từ lịch AI', 'status' => 'draft']);
    }
}
