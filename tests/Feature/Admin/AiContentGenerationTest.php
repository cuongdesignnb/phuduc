<?php

namespace Tests\Feature\Admin;

use App\Models\AiGeneration;
use App\Models\MediaLibrary;
use App\Models\Product;
use App\Models\Post;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AiContentGenerationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'services.ai.content.api_key' => 'content-test-key',
            'services.ai.content.base_url' => 'https://ai.test/v1',
            'services.ai.content.model' => 'content-test-model',
            'services.ai.image.api_key' => 'image-test-key',
            'services.ai.image.base_url' => 'https://image.test/v1',
            'services.ai.image.model' => 'image-test-model',
        ]);
    }

    public function test_content_generation_persists_generation_and_keeps_only_allowed_internal_links(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::create(['name' => 'Xe chở hàng PhuDuc', 'slug' => 'xe-cho-hang-phuduc', 'status' => 'active']);
        $article = Post::create(['title' => 'Hướng dẫn vận hành xe điện', 'slug' => 'huong-dan-van-hanh-xe-dien', 'status' => 'published']);
        Http::fake([
            'https://ai.test/*' => Http::response([
                'choices' => [[
                    'message' => ['content' => json_encode([
                        'title' => 'Cách chọn xe chở hàng phù hợp',
                        'excerpt' => 'Tư vấn chọn xe đúng nhu cầu.',
                        'content' => '<h2>Tiêu chí lựa chọn</h2><p><a href="'.route('products.show', $product->slug).'">Xe chở hàng PhuDuc</a> <a href="https://evil.test/spam">Spam</a></p>',
                        'meta_title' => 'Cách chọn xe chở hàng phù hợp',
                        'meta_desc' => 'Tư vấn cách chọn xe chở hàng phù hợp với nhu cầu vận hành.',
                        'meta_keywords' => 'xe chở hàng, xe điện',
                        'tags' => ['xe điện'],
                    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)],
                ]],
                'usage' => ['total_tokens' => 12],
            ], 200),
        ]);

        $response = $this->actingAs($admin)->postJson(route('admin.ai.content.generate'), [
            'type' => 'article', 'topic' => 'Cách chọn xe chở hàng phù hợp', 'keywords' => ['xe chở hàng'],
            'tone' => 'professional', 'length' => 'medium', 'category_id' => null,
        ]);

        $response->assertOk()->assertJsonPath('status', 'completed');
        $generation = AiGeneration::firstOrFail();
        $this->assertSame('completed', $generation->status);
        $this->assertStringContainsString(route('products.show', $product->slug), $generation->result_payload['content']);
        $this->assertStringNotContainsString('evil.test', $generation->result_payload['content']);
        $this->assertStringContainsString(route('news.show', $article->slug), $generation->result_payload['content']);
    }

    public function test_image_failure_returns_content_with_warning_without_failing_generation(): void
    {
        $admin = User::factory()->admin()->create();
        Http::fake([
            'https://ai.test/*' => Http::response(['output_text' => json_encode([
                'title' => 'Bài có ảnh', 'excerpt' => 'Tóm tắt', 'content' => '<p>Nội dung chính.</p>',
                'meta_title' => 'Bài có ảnh', 'meta_desc' => 'Mô tả bài có ảnh', 'meta_keywords' => 'ảnh',
            ], JSON_UNESCAPED_UNICODE)], 200),
            'https://image.test/*' => Http::response(['error' => 'unavailable'], 503),
        ]);

        $response = $this->actingAs($admin)->postJson(route('admin.ai.content.generate'), [
            'type' => 'article', 'topic' => 'Bài có ảnh', 'keywords' => [], 'tone' => 'professional',
            'length' => 'short', 'with_images' => true, 'image_count' => 1,
        ]);

        $response->assertOk()->assertJsonPath('status', 'completed')->assertJsonCount(1, 'warnings');
        $this->assertSame('completed', AiGeneration::firstOrFail()->status);
        $this->assertStringContainsString('Nội dung chính.', AiGeneration::firstOrFail()->result_payload['content']);
    }

    public function test_generated_image_is_saved_as_webp_with_alt_and_caption(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();
        $png = base64_encode(UploadedFile::fake()->image('generated.png')->getContent());
        Http::fake([
            'https://ai.test/*' => Http::response([
                'choices' => [[
                    'message' => ['content' => json_encode([
                        'title' => 'Xe điện nhà xưởng', 'excerpt' => 'Tóm tắt', 'content' => '<p>Nội dung.</p>',
                        'meta_title' => 'Xe điện nhà xưởng', 'meta_desc' => 'Mô tả', 'meta_keywords' => 'xe điện',
                    ], JSON_UNESCAPED_UNICODE)],
                ]],
            ], 200),
            'https://image.test/*' => Http::response(['data' => [['b64_json' => $png]]], 200),
        ]);

        $response = $this->actingAs($admin)->postJson(route('admin.ai.content.generate'), [
            'type' => 'article', 'topic' => 'Xe điện nhà xưởng', 'keywords' => ['xe điện'],
            'tone' => 'professional', 'length' => 'medium', 'with_images' => true, 'image_count' => 1,
        ]);

        $response->assertOk()->assertJsonPath('status', 'completed');
        $media = MediaLibrary::firstOrFail();
        $this->assertSame('image/webp', $media->mime_type);
        $this->assertStringEndsWith('.webp', $media->file_path);
        $this->assertSame('Xe điện nhà xưởng', $media->alt_text);
        $this->assertStringContainsString('ai-article-image', AiGeneration::firstOrFail()->result_payload['content']);
    }

    public function test_product_ai_image_action_attaches_generated_media_to_product(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();
        $product = Product::create(['name' => 'Xe nâng điện PhuDuc', 'slug' => 'xe-nang-dien-phuduc', 'status' => 'active']);
        $image = base64_encode(UploadedFile::fake()->image('product-ai.png')->getContent());
        Http::fake([
            'https://ai.test/*' => Http::response(['output_text' => json_encode([
                'title' => 'Xe nâng điện PhuDuc', 'excerpt' => 'Mô tả sản phẩm', 'content' => '<p>Mô tả sản phẩm.</p>',
                'meta_title' => 'Xe nâng điện PhuDuc', 'meta_desc' => 'Mô tả SEO sản phẩm.', 'meta_keywords' => 'xe nâng điện',
            ], JSON_UNESCAPED_UNICODE)], 200),
            'https://image.test/*' => Http::response(['data' => [['b64_json' => $image]]], 200),
        ]);

        $response = $this->actingAs($admin)->postJson(route('admin.ai.products.seo', $product), [
            'keywords' => 'xe nâng điện', 'with_images' => true, 'image_count' => 1,
        ]);

        $response->assertOk()->assertJsonPath('status', 'completed');
        $this->assertCount(1, ProductImage::where('product_id', $product->id)->get());
        $this->assertStringStartsWith('products/'.$product->id.'/', ProductImage::firstOrFail()->image_path);
    }
}
