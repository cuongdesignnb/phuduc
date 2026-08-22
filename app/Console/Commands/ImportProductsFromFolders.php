<?php

namespace App\Console\Commands;

use App\Models\MediaLibrary;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class ImportProductsFromFolders extends Command
{
    protected $signature = 'products:import-folders
        {--source=/var/import/phuducdos : Thư mục nguồn, mỗi thư mục con là một sản phẩm}
        {--manifest=docs/phuduc-product-normalization.json : Manifest chuẩn hóa sản phẩm}
        {--dry-run : Chỉ kiểm tra và thống kê, không ghi database hoặc lưu ảnh}
        {--replace : Cập nhật sản phẩm đã có cùng slug và thay lại bộ ảnh}
        {--delete-source : Xóa ảnh nguồn sau khi toàn bộ ảnh đã được lưu và xác minh thành công}';

    protected $description = 'Nhập sản phẩm theo folder, chuẩn hóa slug/giá/biến thể và chuyển ảnh sang WebP';

    /** @var list<string> */
    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'heic', 'avif'];

    public function handle(): int
    {
        $source = rtrim((string) $this->option('source'), "\\/");
        $manifestPath = $this->manifestPath((string) $this->option('manifest'));

        if (! is_dir($source)) {
            $this->error("Không tìm thấy thư mục nguồn: {$source}");

            return self::FAILURE;
        }

        if (! is_file($manifestPath)) {
            $this->error("Không tìm thấy manifest: {$manifestPath}");

            return self::FAILURE;
        }

        try {
            $manifest = json_decode(File::get($manifestPath), true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $exception) {
            $this->error('Manifest không phải JSON hợp lệ: '.$exception->getMessage());

            return self::FAILURE;
        }

        $products = collect($manifest['products'] ?? [])->values();
        if ($products->isEmpty()) {
            $this->error('Manifest không có sản phẩm nào.');

            return self::FAILURE;
        }

        $plans = [];
        $issues = [];
        foreach ($products as $product) {
            $folderName = trim((string) ($product['source_folder'] ?? ''));
            $folder = $source.DIRECTORY_SEPARATOR.$folderName;

            if ($folderName === '' || ! is_dir($folder)) {
                $issues[] = "Không tìm thấy folder nguồn: {$folderName}";
                continue;
            }

            $images = $this->sourceImages($folder);
            if ($images === []) {
                $this->warn("Folder không có ảnh hợp lệ, sẽ nhập sản phẩm không ảnh: {$folderName}");
            }

            $plans[] = [
                'data' => $product,
                'folder' => $folder,
                'images' => $images,
            ];
        }

        $excluded = collect($manifest['excluded'] ?? [])
            ->pluck('source_folder')
            ->map(fn ($folder) => trim((string) $folder))
            ->filter()
            ->all();
        $manifestFolders = $products
            ->pluck('source_folder')
            ->map(fn ($folder) => trim((string) $folder))
            ->filter()
            ->all();
        $unexpected = collect(File::directories($source))
            ->map(fn ($folder) => basename($folder))
            ->reject(fn ($folder) => in_array($folder, $manifestFolders, true) || in_array($folder, $excluded, true))
            ->values();

        $this->table(
            ['Tên chuẩn', 'Slug', 'Ảnh sẽ nhập', 'Giá trường price', 'Biến thể'],
            collect($plans)->map(function (array $plan): array {
                $data = $plan['data'];

                return [
                    (string) ($data['name'] ?? ''),
                    (string) ($data['slug'] ?? ''),
                    count($plan['images']),
                    $this->money((int) ($data['price_vnd'] ?? 0)),
                    count($data['variants'] ?? []),
                ];
            })->all(),
        );

        if ($unexpected->isNotEmpty()) {
            $this->warn('Folder ngoài manifest sẽ không được nhập: '.$unexpected->implode(', '));
        }

        if ($issues !== []) {
            foreach ($issues as $issue) {
                $this->error($issue);
            }

            return self::FAILURE;
        }

        $totalImages = collect($plans)->sum(fn (array $plan) => count($plan['images']));
        $this->info(sprintf('Sẵn sàng nhập %d sản phẩm và %d ảnh.', count($plans), $totalImages));

        if ((bool) $this->option('dry-run')) {
            $this->comment('Dry-run: chưa ghi database, chưa tạo WebP và chưa xóa ảnh nguồn.');

            return self::SUCCESS;
        }

        $storedPaths = [];
        /** @var list<array{source: string, path: string}> $sourceToStored */
        $sourceToStored = [];
        $importedProducts = 0;
        $importedImages = 0;
        $skippedProducts = 0;

        foreach ($plans as $plan) {
            $data = $plan['data'];
            $slug = trim((string) ($data['slug'] ?? ''));
            $name = trim((string) ($data['name'] ?? ''));

            if ($slug === '' || $name === '') {
                $this->error('Manifest có sản phẩm thiếu name hoặc slug.');

                return self::FAILURE;
            }

            $existing = Product::query()->where('slug', $slug)->first();
            if ($existing && ! (bool) $this->option('replace')) {
                $this->warn("Bỏ qua {$name}: slug {$slug} đã tồn tại. Dùng --replace nếu muốn cập nhật.");
                $skippedProducts++;
                continue;
            }

            $productStoredPaths = [];
            $oldPaths = [];

            try {
                DB::transaction(function () use ($data, $name, $slug, $plan, &$productStoredPaths, &$oldPaths, &$importedImages, &$sourceToStored, $existing): void {
                    $product = $existing ?: Product::query()->where('slug', $slug)->first();
                    if ($product && $product->name !== $name) {
                        throw new RuntimeException("Slug {$slug} đang thuộc sản phẩm khác: {$product->name}");
                    }

                    if (! $product) {
                        $product = new Product;
                        $product->slug = $slug;
                    }

                    if ($product->exists && (bool) $this->option('replace') && $plan['images'] !== []) {
                        $oldPaths = $product->images()->pluck('image_path')->filter()->values()->all();
                        $product->images()->delete();
                    }

                    $variants = collect($data['variants'] ?? [])->values()->all();
                    $product->forceFill([
                        'name' => $name,
                        'slug' => $slug,
                        'description' => $this->description($name, $variants, $data['source_note'] ?? null),
                        'price' => (int) ($data['price_vnd'] ?? 0),
                        'sku' => null,
                        'stock' => 0,
                        'specifications' => [],
                        'status' => 'active',
                        'meta_title' => Str::limit($name.' | Phú Đức', 255, ''),
                        'meta_description' => Str::limit($this->metaDescription($name, $variants), 5000, ''),
                    ])->save();

                    ProductVariant::query()->where('product_id', $product->id)->delete();
                    foreach ($variants as $variantIndex => $variant) {
                        $label = trim((string) ($variant['label'] ?? ''));
                        if ($label === '') {
                            continue;
                        }

                        ProductVariant::query()->create([
                            'product_id' => $product->id,
                            'name' => $label,
                            'sku' => null,
                            'price' => (int) ($variant['price_vnd'] ?? 0),
                            'stock' => 0,
                            'note' => filled($variant['note'] ?? null) ? trim((string) $variant['note']) : null,
                            'status' => 'active',
                            'sort_order' => $variantIndex,
                        ]);
                    }

                    $sortOrder = 0;
                    foreach ($plan['images'] as $index => $image) {
                        $sequence = $index + 1;
                        $filename = sprintf('%s-anh-san-pham-%02d.webp', $slug, $sequence);
                        $path = 'media/'.$slug.'/'.$filename;
                        $contents = $this->toWebp($image->getRealPath());

                        if ($contents === '') {
                            throw new RuntimeException('Ảnh sau chuyển đổi bị rỗng: '.$image->getPathname());
                        }

                        if (! Storage::disk('public')->put($path, $contents)) {
                            throw new RuntimeException('Không thể ghi ảnh WebP: '.$path);
                        }

                        $productStoredPaths[] = $path;
                        $altText = sprintf('%s – ảnh sản phẩm %d', $name, $sequence);
                        MediaLibrary::query()->updateOrCreate(
                            ['file_path' => $path],
                            [
                                'file_name' => $filename,
                                'mime_type' => 'image/webp',
                                'size' => strlen($contents),
                                'alt_text' => $altText,
                            ],
                        );
                        $product->images()->create([
                            'image_path' => $path,
                            'is_360' => false,
                            'sort_order' => $sortOrder++,
                        ]);
                        $importedImages++;
                        $sourceToStored[] = ['source' => $image->getPathname(), 'path' => $path];
                    }

                    $this->syncVariantImageAnchors($product);
                });

                foreach ($oldPaths as $oldPath) {
                    $this->removeReplacedMedia($oldPath);
                }

                foreach ($productStoredPaths as $path) {
                    $storedPaths[] = $path;
                }
                $importedProducts++;
            } catch (\Throwable $exception) {
                foreach ($productStoredPaths as $path) {
                    Storage::disk('public')->delete($path);
                    MediaLibrary::query()->where('file_path', $path)->delete();
                }

                $this->error("Không thể nhập {$name}: ".$exception->getMessage());

                return self::FAILURE;
            }
        }

        if ((bool) $this->option('delete-source')) {
            $deleted = 0;
            $failedDeletes = [];
            foreach ($sourceToStored as $sourceStored) {
                $sourceImage = $sourceStored['source'];
                $path = $sourceStored['path'];
                if (! $this->sourceDeleteIsVerified($path)) {
                    $failedDeletes[] = $sourceImage;
                    continue;
                }

                if (File::delete($sourceImage)) {
                    $deleted++;
                } else {
                    $failedDeletes[] = $sourceImage;
                }
            }

            $this->info("Đã xóa {$deleted} ảnh nguồn sau khi xác minh.");
            if ($failedDeletes !== []) {
                $this->warn('Một số ảnh nguồn không xóa được: '.count($failedDeletes));
            }
        }

        $this->info(sprintf(
            'Hoàn tất: %d sản phẩm, %d ảnh WebP, %d sản phẩm bỏ qua.',
            $importedProducts,
            $importedImages,
            $skippedProducts,
        ));

        return self::SUCCESS;
    }

    private function manifestPath(string $manifest): string
    {
        return Str::startsWith($manifest, ['/', '\\']) || preg_match('/^[A-Za-z]:[\\\\\/]/', $manifest)
            ? $manifest
            : base_path($manifest);
    }

    /** @return list<\SplFileInfo> */
    private function sourceImages(string $folder): array
    {
        return collect(File::allFiles($folder))
            ->filter(function ($file): bool {
                $extension = strtolower((string) $file->getExtension());
                if (! in_array($extension, self::IMAGE_EXTENSIONS, true)) {
                    return false;
                }

                $path = str_replace('\\', '/', $file->getPath());
                if (preg_match('~(?:^|/)[^/]*_files(?:/|$)~i', $path)) {
                    return false;
                }

                return ! preg_match('/(?:[_-](?:20|40|50|60|80|95|100|120|160|220|300|360|480|720|960)x\d+)/i', $file->getFilename());
            })
            ->sortBy(fn ($file) => strtolower(str_replace('\\', '/', $file->getPathname())))
            ->values()
            ->all();
    }

    /** @param list<array<string, mixed>> $variants */
    private function description(string $name, array $variants, ?string $sourceNote): string
    {
        $html = '<p>'.e($name).'.</p>';
        if (filled($sourceNote)) {
            $html .= '<p>'.e(trim((string) $sourceNote)).'</p>';
        }

        return $html;
    }

    /** @param list<array<string, mixed>> $variants */
    private function metaDescription(string $name, array $variants): string
    {
        $prices = collect($variants)
            ->pluck('price_vnd')
            ->map(fn ($price) => (int) $price)
            ->filter(fn (int $price) => $price > 0)
            ->map(fn (int $price) => $this->money($price))
            ->implode(', ');

        return $prices !== ''
            ? $name.'. Các phiên bản và giá tham khảo: '.$prices.'.'
            : $name.'. Xem đầy đủ thông tin và hình ảnh sản phẩm tại Phú Đức.';
    }

    private function money(int $amount): string
    {
        return number_format($amount, 0, ',', '.').' ₫';
    }

    private function toWebp(string $sourcePath): string
    {
        return $this->convertWithImageMagick($sourcePath);
    }

    private function convertWithImageMagick(string $sourcePath, ?string $previousError = null): string
    {
        $tempDir = storage_path('app/import-temp');
        File::ensureDirectoryExists($tempDir);
        $target = $tempDir.'/'.Str::uuid().'.webp';
        $errors = [];

        foreach (['magick', 'convert'] as $binary) {
            $result = Process::run([$binary, $sourcePath, '-auto-orient', '-strip', '-quality', '82', $target]);
            if ($result->successful() && File::exists($target)) {
                $contents = File::get($target);
                File::delete($target);

                return $contents;
            }
            $errors[] = $binary.': '.trim($result->errorOutput());
        }

        File::delete($target);
        throw new RuntimeException('Không chuyển được ảnh sang WebP: '.($previousError ? $previousError.'; ' : '').implode('; ', $errors));
    }

    private function removeReplacedMedia(string $path): void
    {
        if (! Str::startsWith($path, 'media/')) {
            return;
        }

        if (ProductImage::query()->where('image_path', $path)->exists()) {
            return;
        }

        MediaLibrary::query()->where('file_path', $path)->delete();
        Storage::disk('public')->delete($path);
    }

    private function syncVariantImageAnchors(Product $product): void
    {
        $images = $product->images()
            ->where('is_360', false)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
        $variants = $product->variants()->orderBy('sort_order')->orderBy('id')->get();

        if ($images->isEmpty()) {
            $variants->each(fn ($variant) => $variant->update(['product_image_id' => null]));

            return;
        }

        foreach ($variants as $index => $variant) {
            $imageIndex = (int) floor(($index * $images->count()) / max(1, $variants->count()));
            $variant->update(['product_image_id' => $images->get($imageIndex)?->id]);
        }
    }

    private function sourceDeleteIsVerified(string $path): bool
    {
        return Storage::disk('public')->exists($path)
            && MediaLibrary::query()->where('file_path', $path)->where('mime_type', 'image/webp')->exists()
            && ProductImage::query()->where('image_path', $path)->exists();
    }
}
