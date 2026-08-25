<?php

namespace Tests\Feature\Admin;

use App\Models\Setting;
use App\Models\User;
use App\Services\Admin\Ai\AiConfigurationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiConfigurationTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_keys_are_encrypted_and_public_snapshot_is_masked(): void
    {
        app(AiConfigurationService::class)->save([
            'content_api_key' => 'content-secret-key',
            'image_api_key' => 'image-secret-key',
            'content_base_url' => 'https://content.test/v1',
            'image_base_url' => 'https://image.test/v1',
            'content_wire_api' => 'chat_completions',
            'content_model' => 'model',
            'content_max_tokens' => 4000,
            'image_model' => 'image-model',
            'image_quality' => 'medium',
        ]);

        $this->assertNotSame('content-secret-key', Setting::where('key', 'ai.content.api_key')->value('value'));
        $snapshot = app(AiConfigurationService::class)->publicSnapshot();
        $this->assertTrue($snapshot['content']['has_api_key']);
        $this->assertStringContainsString('••••••', $snapshot['content']['key_hint']);
        $this->assertArrayNotHasKey('api_key', $snapshot['content']);
    }

    public function test_admin_can_open_ai_page(): void
    {
        $response = $this->actingAs(User::factory()->admin()->create())->get(route('admin.ai.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Admin/Ai/Index')->has('page.module.settings'));
    }
}
