<?php

namespace Database\Seeders;

use App\Models\MediaLibrary;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use JsonException;
use RuntimeException;

class PhuDucSeoProductSeeder extends Seeder
{
    private const MANIFEST = 'docs/phuduc-product-normalization.json';

    private const PUBLIC_CATALOGUE_ORIGIN = 'https://phuduc.com';

    /**
     * Update the complete catalogue without deleting existing products,
     * variants, or gallery images.
     */
    public function run(): void
    {
        foreach ($this->manifestProducts() as $productData) {
            DB::transaction(function () use ($productData): void {
                $product = $this->upsertProduct($productData);
                $this->syncVariants($product, $productData['variants'] ?? []);
                $this->syncExistingImageAltText($product);
            });
        }
    }

    /** @param array<string, mixed> $data */
    private function upsertProduct(array $data): Product
    {
        $slug = trim((string) ($data['slug'] ?? ''));
        $name = trim((string) ($data['name'] ?? ''));

        if ($slug === '' || $name === '') {
            throw new RuntimeException('Manifest sản phẩm thiếu slug hoặc tên.');
        }

        $product = Product::query()->where('slug', $slug)->first() ?? new Product;
        $variants = $this->variantLabels($data['variants'] ?? []);
        $notes = $this->variantNotes($data['variants'] ?? []);

        $product->forceFill([
            'name' => $name,
            'slug' => $slug,
            'description' => $this->description($name, $slug, $variants, $notes),
            'price' => (int) ($data['price_vnd'] ?? 0),
            'specifications' => $this->specifications($slug, $variants, $notes),
            'meta_title' => $this->metaTitle($name, $variants),
            'meta_description' => $this->metaDescription($name, $variants),
        ]);

        if (! $product->exists) {
            $product->forceFill(['sku' => null, 'stock' => 0, 'status' => 'active']);
        }

        $product->save();

        return $product;
    }

    /** @param list<array<string, mixed>> $variants */
    private function syncVariants(Product $product, array $variants): void
    {
        foreach ($variants as $sortOrder => $variant) {
            $name = trim((string) ($variant['label'] ?? ''));
            if ($name === '') {
                continue;
            }

            $product->variants()->updateOrCreate(
                ['name' => $name],
                [
                    'sku' => null,
                    'price' => (int) ($variant['price_vnd'] ?? 0),
                    'stock' => 0,
                    'note' => filled($variant['note'] ?? null) ? trim((string) $variant['note']) : null,
                    'status' => 'active',
                    'sort_order' => $sortOrder,
                ],
            );
        }
    }

    private function syncExistingImageAltText(Product $product): void
    {
        if (! $product->images()->exists()) {
            $this->importPublicGallery($product);
        }

        $product->images()
            ->orderBy('is_360')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->values()
            ->each(function ($image, int $index) use ($product): void {
                $altText = sprintf('%s – ảnh sản phẩm %d', $product->name, $index + 1);
                $image->update(['alt_text' => $altText]);

                if (! Storage::disk('public')->exists($image->image_path)) {
                    return;
                }

                $path = $image->image_path;
                MediaLibrary::query()->updateOrCreate(
                    ['file_path' => $path],
                    [
                        'file_name' => basename($path),
                        'mime_type' => Storage::disk('public')->mimeType($path) ?: 'image/webp',
                        'size' => Storage::disk('public')->size($path),
                        'alt_text' => $altText,
                        'caption' => $product->name,
                    ],
                );
            });
    }

    /**
     * A fresh local database has no product photos. The public catalogue is an
     * approved first-party source and is consulted only when a gallery is empty.
     * Existing production galleries are never downloaded, replaced, or deleted.
     */
    private function importPublicGallery(Product $product): void
    {
        if (app()->environment('testing')) {
            return;
        }

        $url = self::PUBLIC_CATALOGUE_ORIGIN.'/san-pham/'.$product->slug;

        try {
            $page = Http::timeout(30)->retry(2, 500)->get($url);
        } catch (\Throwable $exception) {
            throw new RuntimeException('Không thể đọc gallery công khai: '.$url, previous: $exception);
        }

        if (! $page->successful()) {
            throw new RuntimeException(sprintf('Không thể đọc gallery công khai (%d): %s', $page->status(), $url));
        }

        $body = html_entity_decode(str_replace('\\/', '/', $page->body()), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        preg_match_all('#https://phuduc\\.com/storage/[^"\'\\s<]+#u', $body, $matches);
        $imageUrls = collect($matches[0] ?? [])
            ->map(fn (string $imageUrl) => rtrim($imageUrl, '.,;'))
            ->filter(fn (string $imageUrl) => str_starts_with($imageUrl, self::PUBLIC_CATALOGUE_ORIGIN.'/storage/media/'))
            ->unique()
            ->values();

        if ($imageUrls->isEmpty()) {
            $this->command?->warn("{$product->name}: chưa có gallery công khai để nhập.");

            return;
        }

        foreach ($imageUrls as $sortOrder => $imageUrl) {
            $relativePath = ltrim((string) parse_url($imageUrl, PHP_URL_PATH), '/');
            $relativePath = preg_replace('#^storage/#', '', $relativePath) ?? '';

            if ($relativePath === '' || str_contains($relativePath, '..')) {
                throw new RuntimeException('Đường dẫn ảnh công khai không hợp lệ: '.$imageUrl);
            }

            try {
                $response = Http::timeout(45)->retry(2, 500)->get($imageUrl);
            } catch (\Throwable $exception) {
                throw new RuntimeException('Không thể tải ảnh gallery: '.$imageUrl, previous: $exception);
            }

            if (! $response->successful() || ! str_starts_with((string) $response->header('Content-Type'), 'image/')) {
                throw new RuntimeException('Ảnh gallery không hợp lệ: '.$imageUrl);
            }

            $contents = $response->body();
            $altText = sprintf('%s – ảnh sản phẩm %d', $product->name, $sortOrder + 1);
            Storage::disk('public')->put($relativePath, $contents);
            MediaLibrary::query()->updateOrCreate(
                ['file_path' => $relativePath],
                [
                    'file_name' => basename($relativePath),
                    'mime_type' => $response->header('Content-Type'),
                    'size' => strlen($contents),
                    'alt_text' => $altText,
                    'caption' => $product->name,
                ],
            );
            $product->images()->updateOrCreate(
                ['image_path' => $relativePath],
                ['alt_text' => $altText, 'is_360' => false, 'sort_order' => $sortOrder],
            );
        }
    }

    /** @return list<array<string, mixed>> */
    private function manifestProducts(): array
    {
        $path = base_path(self::MANIFEST);
        if (! is_file($path)) {
            throw new RuntimeException('Không tìm thấy manifest catalogue: '.$path);
        }

        try {
            $manifest = json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException('Manifest catalogue không phải JSON hợp lệ.', previous: $exception);
        }

        $products = array_values($manifest['products'] ?? []);
        if (count($products) !== 28) {
            throw new RuntimeException(sprintf('Manifest catalogue phải có 28 sản phẩm, hiện có %d.', count($products)));
        }

        return $products;
    }

    /**
     * @param  list<string>  $variants
     * @param  list<string>  $notes
     */
    private function description(string $name, string $slug, array $variants, array $notes): string
    {
        $approvedNotes = $notes === []
            ? ''
            : '<h2>Nội dung đi kèm theo cấu hình</h2><ul>'.$this->listItems($notes).'</ul>';

        return sprintf(
            '<p><strong>%s</strong> được Phú Đức cung cấp theo các cấu hình đang niêm yết. Trang sản phẩm tập trung vào lựa chọn đúng tải trọng, kiểu vận hành và điều kiện sử dụng thực tế; đội ngũ kỹ thuật sẽ xác nhận thông số chi tiết theo phiên bản trước khi báo giá.</p><h2>Cấu hình đang có</h2><ul>%s</ul>%s<h2>Tư vấn lựa chọn</h2><p>Liên hệ Phú Đức để đối chiếu nhu cầu sử dụng, không gian làm việc, tải trọng và phụ kiện phù hợp với từng cấu hình.</p>%s',
            e($name),
            $this->listItems($variants),
            $approvedNotes,
            $this->relatedLinks($slug),
        );
    }

    /**
     * @param  list<string>  $variants
     * @param  list<string>  $notes
     * @return list<array{key: string, label: string, value: string}>
     */
    private function specifications(string $slug, array $variants, array $notes): array
    {
        $specifications = [$this->spec('Cấu hình đang có', implode(' · ', $variants))];

        foreach ($this->verifiedSpecifications()[$slug] ?? [] as $label => $value) {
            $specifications[] = $this->spec($label, $value);
        }

        foreach ($notes as $noteIndex => $note) {
            $specifications[] = $this->spec(
                $noteIndex === 0 ? 'Ghi chú cấu hình' : 'Ghi chú cấu hình '.($noteIndex + 1),
                $note,
            );
        }

        return $specifications;
    }

    /** @return array<string, array<string, string>> */
    private function verifiedSpecifications(): array
    {
        return [
            'cau-dien-thuy-luc-2-tan' => [
                'Tải trọng tối đa' => '2 tấn',
                'Kiểu cần' => '3 đốt',
                'Độ vươn cần' => '3 m',
                'Góc xoay' => '360°',
            ],
            'cau-truc-chay-dien-co-chan-di-chuyen' => [
                'Kích thước cấu hình 2 / 3 / 5 tấn' => 'Cao 3 m, ngang 3 m',
            ],
            'may-xuc-lat-4-banh-gau-0-4-m3' => [
                'Dung tích gầu' => '0,4 m³',
                'Nguồn động lực lựa chọn' => 'Chạy điện hoặc chạy dầu diesel',
            ],
            'xe-nang-dien-500-kg-48v' => [
                'Tải trọng' => '500 kg',
                'Điện áp' => '48V',
            ],
            'xe-nang-tay' => [
                'Tải trọng và chiều cao nâng' => '200 kg / 1 m hoặc 260 kg / 1,2 m tùy cấu hình',
            ],
            'xe-nang-pallet-2-tan' => [
                'Tải nâng' => '2 tấn',
                'Tùy chọn' => 'Có phiên bản kèm cân điện tử',
            ],
            'xe-rua-banh-xich-cho-hang-dieu-khien-tu-xa' => [
                'Nguồn động lực' => 'Chạy điện',
                'Mức tải lựa chọn' => '500 kg / 1 tấn / 2 tấn',
            ],
        ];
    }

    /** @param list<string> $variants */
    private function metaTitle(string $name, array $variants): string
    {
        $suffix = $variants === [] ? '' : ' | '.implode(', ', array_slice($variants, 0, 2));

        return Str::limit($name.$suffix.' | Phú Đức', 255, '');
    }

    /** @param list<string> $variants */
    private function metaDescription(string $name, array $variants): string
    {
        $configurationText = $variants === [] ? 'cấu hình hiện có' : implode(', ', $variants);

        return Str::limit(
            sprintf('%s tại Phú Đức. Cấu hình đang niêm yết: %s. Liên hệ để xác nhận thông số và phương án phù hợp.', $name, $configurationText),
            1000,
            '',
        );
    }

    /**
     * @param  list<array<string, mixed>>  $variants
     * @return list<string>
     */
    private function variantLabels(array $variants): array
    {
        return collect($variants)->pluck('label')->map(fn ($label) => trim((string) $label))->filter()->values()->all();
    }

    /**
     * @param  list<array<string, mixed>>  $variants
     * @return list<string>
     */
    private function variantNotes(array $variants): array
    {
        return collect($variants)->pluck('note')->map(fn ($note) => trim((string) $note))->filter()->unique()->values()->all();
    }

    /** @param list<string> $values */
    private function listItems(array $values): string
    {
        return implode('', array_map(fn (string $value) => '<li>'.e($value).'</li>', $values));
    }

    private function relatedLinks(string $currentSlug): string
    {
        $clusters = [
            ['can-cau-chan-nhen', 'cau-chan-nhen-chay-dien-1-5-tan', 'cau-dien-thuy-luc-2-tan', 'cau-thuy-luc-500-kg', 'cau-truc-chay-dien-co-chan-di-chuyen', 'cau-truc-co-dinh-xoay-360-do-chay-dien'],
            ['may-xuc-lat-3-banh-gau-0-2-m3', 'may-xuc-lat-4-banh-gau-0-4-m3', 'may-xuc-lat-gau-0-7-m3'],
            ['xe-nang-dien-0-8-tan', 'xe-nang-dien', 'xe-nang-dau-diesel-3-tan', 'xe-nang-dien-500-kg-48v', 'xe-nang-ket-hop-cho-hang-1-5-tan', 'xe-nang-pallet-2-tan', 'xe-nang-tay', 'may-gap-hang-khi-nen-100-kg'],
            ['xe-cho-hang-3-banh-1-tan', 'xe-cho-hang-chay-dien', 'xe-rua-banh-xich-cho-hang-dieu-khien-tu-xa', 'xe-tai-banh-xich-cho-hang-1-8-tan', 'xe-nang-ket-hop-cho-hang-1-5-tan'],
            ['may-bom-be-tong-chay-dau', 'may-cat-dieu-khien-tu-xa', 'may-phun-vua-tuong-chay-dien'],
            ['dieu-hoa-24v-cho-xe-tai-cat-noc', 'may-phat-dien-nap-cho-xe-tai-chay-xang-7l', 'motor-toi', 'nha-container'],
        ];
        $names = collect($this->manifestProducts())->pluck('name', 'slug');
        $cluster = collect($clusters)->first(fn (array $slugs) => in_array($currentSlug, $slugs, true)) ?? [];
        $links = collect($cluster)
            ->reject(fn (string $slug) => $slug === $currentSlug)
            ->take(3)
            ->map(fn (string $slug) => sprintf('<li><a href="/san-pham/%s">%s</a></li>', $slug, e((string) $names->get($slug))))
            ->all();

        $links[] = '<li><a href="/san-pham">Tất cả sản phẩm</a></li>';

        return '<h2>Thiết bị liên quan</h2><ul>'.implode('', $links).'</ul>';
    }

    /** @return array{key: string, label: string, value: string} */
    private function spec(string $label, string $value): array
    {
        return ['key' => $label, 'label' => $label, 'value' => $value];
    }
}
