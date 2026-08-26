<?php

namespace App\Services\Admin\Ai;

use App\Models\AiGeneration;
use App\Models\MediaLibrary;
use App\Models\Post;
use App\Models\PostMedia;
use App\Models\Product;
use App\Models\User;
use App\Services\Storefront\RichHtmlSanitizer;
use Illuminate\Support\Str;

final class AiContentGenerationService
{
    public function __construct(
        private readonly AiProviderClient $provider,
        private readonly AiInternalLinkService $links,
        private readonly AiGeneratedMediaService $media,
        private readonly RichHtmlSanitizer $sanitizer,
    ) {}

    /** @return array<string, mixed> */
    public function generate(array $input, ?User $user = null): array
    {
        $kind = (string) ($input['type'] ?? 'article');
        $product = isset($input['product_id']) ? Product::query()->with(['variants', 'images'])->find($input['product_id']) : null;
        $targets = $this->links->targets($product?->id, isset($input['category_id']) ? (int) $input['category_id'] : null);
        $payload = [
            'type' => $kind,
            'topic' => trim((string) ($input['topic'] ?? $product?->name ?? '')),
            'keywords' => array_values(array_filter(array_map('trim', (array) ($input['keywords'] ?? [])))),
            'tone' => $input['tone'] ?? 'professional',
            'length' => $input['length'] ?? 'medium',
            'full_article' => (bool) ($input['full_article'] ?? true),
            'existing_content' => (string) ($input['existing_content'] ?? ''),
            'product_id' => $product?->id,
            'category_id' => $input['category_id'] ?? null,
            'with_images' => (bool) ($input['with_images'] ?? false),
            'image_count' => min(max((int) ($input['image_count'] ?? 1), 1), 4),
        ];

        $generation = AiGeneration::create([
            'generation_id' => (string) Str::uuid(),
            'user_id' => $user?->id,
            'kind' => $kind,
            'status' => 'pending',
            'request_payload' => $payload,
        ]);

        try {
            $generation->update(['status' => 'running', 'started_at' => now()]);
            $response = $this->provider->text($this->messages($payload, $product, $targets));
            $result = $this->normalize($this->decode($response['text']), $payload, $product);
            $result['content'] = $this->links->apply($this->sanitizer->sanitize($result['content']), $targets);
            $warnings = [];

            if ($payload['with_images']) {
                [$result, $warnings] = $this->withImages($result, $payload, $generation->generation_id);
                $result['content'] = $this->links->apply($this->sanitizer->sanitize($result['content']), $targets);
            }

            $generation->update([
                'status' => 'completed',
                'provider' => $response['provider'],
                'model' => $response['model'],
                'result_payload' => $result,
                'usage' => $response['usage'],
                'warnings' => $warnings,
                'completed_at' => now(),
            ]);

            return ['generation_id' => $generation->generation_id, 'status' => 'completed', 'result' => $result, 'warnings' => $warnings];
        } catch (\Throwable $exception) {
            $generation->update(['status' => 'failed', 'error_message' => $exception->getMessage(), 'completed_at' => now()]);
            throw $exception;
        }
    }

    public function saveArticle(AiGeneration $generation, ?User $user = null, bool $autoPublish = false): Post
    {
        if ($generation->status !== 'completed' || ! is_array($generation->result_payload)) {
            throw new \RuntimeException('Generation chưa có kết quả hợp lệ để tạo bài viết.');
        }
        $result = $generation->result_payload;
        $slugBase = Str::slug((string) ($result['title'] ?? 'bai-viet-ai')) ?: 'bai-viet-ai';
        $slug = $slugBase;
        $suffix = 2;
        while (Post::query()->where('slug', $slug)->exists()) {
            $slug = $slugBase.'-'.($suffix++);
        }

        $mediaIds = collect($result['images'] ?? [])->pluck('media_id')->filter()->map(fn ($id) => (int) $id)->values()->all();
        $featured = $mediaIds[0] ?? null;
        $featuredPath = $featured ? MediaLibrary::query()->whereKey($featured)->value('file_path') : null;
        $post = Post::create([
            'post_category_id' => $result['category_id'] ?? null,
            'title' => $result['title'],
            'slug' => $slug,
            'summary' => $result['excerpt'],
            'content' => $result['content'],
            'featured_image' => $featuredPath,
            'status' => $autoPublish ? 'published' : 'draft',
            'author_id' => $user?->id,
            'meta_title' => $result['meta_title'],
            'meta_description' => $result['meta_desc'],
        ]);
        foreach ($mediaIds as $index => $mediaId) {
            PostMedia::create(['post_id' => $post->id, 'media_id' => $mediaId, 'sort_order' => $index]);
        }

        return $post;
    }

    /** @return array{0: array<string, mixed>, 1: list<string>} */
    private function withImages(array $result, array $payload, string $generationId): array
    {
        $warnings = [];
        $images = [];
        $directory = 'ai/articles/'.$generationId;
        $prompts = [$result['title']];
        foreach ($this->headings($result['content']) as $heading) {
            if (count($prompts) >= $payload['image_count']) {
                break;
            }
            $prompts[] = $heading;
        }

        foreach ($prompts as $index => $subject) {
            $alt = $index === 0 ? $result['title'] : $result['title'].' - '.$subject;
            try {
                $generated = $this->media->generate(
                    'Ảnh minh họa thương mại điện tử chân thực, sạch, không chèn chữ, không logo. Chủ đề: '.$subject.'. Bối cảnh phù hợp với thương hiệu PhuDuc.',
                    $alt,
                    'Ảnh minh họa '.$subject,
                    $directory,
                );
                $images[] = ['media_id' => $generated['media']->id, 'url' => $generated['url'], 'alt' => $generated['alt'], 'caption' => $generated['caption']];
                $figure = '<figure class="ai-article-image"><img src="'.e($generated['url']).'" alt="'.e($generated['alt']).'" loading="lazy"><figcaption>'.e($generated['caption']).'</figcaption></figure>';
                $result['content'] .= $figure;
            } catch (\Throwable $exception) {
                $warnings[] = 'Ảnh '.($index + 1).': '.$exception->getMessage();
            }
        }
        $result['images'] = $images;

        return [$result, $warnings];
    }

    private function messages(array $payload, ?Product $product, array $targets): array
    {
        $productData = $product ? [
            'name' => $product->name,
            'sku' => $product->sku,
            'price' => (string) $product->price,
            'description' => $product->description,
            'variants' => $product->variants->map(fn ($variant) => ['name' => $variant->name, 'price' => (string) $variant->price, 'stock' => $variant->stock])->all(),
            'specifications' => $product->specifications,
        ] : null;
        $schema = '{"title":"...","excerpt":"...","content":"HTML không có h1, chỉ h2/h3 và thẻ an toàn","meta_title":"...","meta_desc":"...","tags":["..."],"category_id":null}';
        if ($payload['type'] === 'product_description') {
            $schema = '{"title":"...","excerpt":"...","content":"HTML mô tả sản phẩm","meta_title":"...","meta_desc":"...","tags":["..."]}';
        }

        return [
            ['role' => 'system', 'content' => 'Bạn là chuyên gia SEO tiếng Việt cho website Phú Đức. Chỉ trả về JSON hợp lệ theo schema, không markdown fence. Không bịa giá, SKU, thông số, thương hiệu, review, tồn kho, chứng nhận, năm thành lập hoặc claim không có dữ liệu. Không dùng h1, không tự thêm tên thương hiệu vào meta title. Nội dung phải tự nhiên, có anchor text và chỉ dùng internal link trong danh sách được phép. Mỗi URL tối đa một lần.'],
            ['role' => 'user', 'content' => json_encode([
                'task' => $payload,
                'product_data' => $productData,
                'allowed_internal_link_targets' => $targets,
                'output_schema' => $schema,
                'requirements' => [
                    'viết đúng ngôn ngữ và tone đã chọn',
                    'từ khóa được phân bổ tự nhiên, không nhồi nhét',
                    'content là HTML an toàn gồm p, h2, h3, ul, ol, strong, a',
                    'anchor text phải mô tả đúng trang đích',
                    'meta_title thường cô đọng khoảng 50-60 ký tự, meta_desc thường khoảng 140-160 ký tự; đây là hướng dẫn biên tập, không phải giới hạn cứng',
                    'meta_desc là plain text, không chứa HTML',
                ],
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),],
        ];
    }

    private function decode(string $text): array
    {
        $text = trim(preg_replace('/^```(?:json)?|```$/mi', '', $text) ?? $text);
        $decoded = json_decode($text, true);
        if (! is_array($decoded)) {
            $start = strpos($text, '{');
            $end = strrpos($text, '}');
            $decoded = $start !== false && $end !== false ? json_decode(substr($text, $start, $end - $start + 1), true) : null;
        }
        if (! is_array($decoded)) {
            throw new \RuntimeException('AI trả về JSON không hợp lệ.');
        }

        return $decoded;
    }

    private function normalize(array $data, array $payload, ?Product $product): array
    {
        $content = (string) ($data['content'] ?? $data['description'] ?? '');
        if (blank($content)) {
            throw new \RuntimeException('AI không tạo được nội dung.');
        }
        $title = trim((string) ($data['title'] ?? $payload['topic'] ?? $product?->name ?? 'Nội dung AI'));

        return [
            'title' => $title,
            'excerpt' => trim((string) ($data['excerpt'] ?? $data['summary'] ?? '')),
            'content' => $content,
            'meta_title' => trim((string) ($data['meta_title'] ?? $title)),
            'meta_desc' => trim((string) ($data['meta_desc'] ?? $data['meta_description'] ?? $data['excerpt'] ?? '')),
            'tags' => array_values(array_filter(array_map('strval', (array) ($data['tags'] ?? [])))),
            'category_id' => $payload['category_id'] ?? null,
            'images' => [],
        ];
    }

    /** @return list<string> */
    private function headings(string $html): array
    {
        preg_match_all('/<h[2-3][^>]*>(.*?)<\/h[2-3]>/is', $html, $matches);

        return collect($matches[1] ?? [])->map(fn ($heading) => trim(strip_tags($heading)))->filter()->values()->all();
    }
}
