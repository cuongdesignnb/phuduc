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
            'meta_description' => $this->metaDescription($slug),
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
        $profile = $this->contentProfile($slug);
        $verifiedSpecifications = $this->verifiedSpecifications()[$slug] ?? [];
        $verifiedBlock = $verifiedSpecifications === []
            ? ''
            : '<h2>Thông số đang được ghi nhận</h2><ul>'.$this->listItems(
                collect($verifiedSpecifications)
                    ->map(fn (string $value, string $label) => $label.': '.$value)
                    ->values()
                    ->all(),
            ).'</ul>';
        $approvedNotes = $notes === []
            ? ''
            : '<h2>Nội dung đi kèm theo cấu hình</h2><ul>'.$this->listItems($notes).'</ul>';

        return sprintf(
            '<p><strong>%s</strong> %s</p><p>%s</p><h2>Cấu hình đang niêm yết</h2><p>Danh sách dưới đây là các phiên bản hiện có để khách hàng đối chiếu trước khi yêu cầu báo giá.</p><ul>%s</ul>%s%s<h2>Tiêu chí chọn cấu hình</h2><ul>%s</ul><h2>Thông tin cần xác nhận trước khi đặt hàng</h2><p>%s</p><h2>Nhận tư vấn và báo giá</h2><p>Gửi Phú Đức nhu cầu sử dụng, tải trọng hoặc công việc dự kiến và điều kiện lắp đặt/vận hành. Đội ngũ tư vấn sẽ đối chiếu phiên bản phù hợp, hình ảnh thực tế và thông số kỹ thuật trước khi báo giá.</p>%s',
            e($name),
            e($profile['intro']),
            e($profile['context']),
            $this->listItems($variants),
            $verifiedBlock,
            $approvedNotes,
            $this->listItems($profile['selection']),
            e($profile['confirm']),
            $this->relatedLinks($slug),
        );
    }

    /**
     * Each product owns a distinct commercial-search intent. These profiles
     * deliberately stay within information available in the source manifest;
     * they guide the buyer without inventing model-specific performance data.
     *
     * @return array{intro: string, context: string, selection: list<string>, confirm: string, meta: string}
     */
    private function contentProfile(string $slug): array
    {
        $profiles = [
            'can-cau-chan-nhen' => [
                'intro' => 'là trang tham khảo các mức tải 1 tấn, 5 tấn, 8 tấn và 12 tấn đang được niêm yết tại Phú Đức.',
                'context' => 'Trang phù hợp với khách hàng cần sàng lọc cẩu chân nhện theo tải trọng trước khi đi sâu vào model và phương án triển khai thực tế.',
                'selection' => ['Xác định tải trọng nâng và bán kính làm việc cần đáp ứng.', 'Đối chiếu mặt bằng đặt thiết bị, chiều cao thao tác và vị trí làm việc.', 'Yêu cầu catalogue của đúng phiên bản để kiểm tra thông số vận hành.'],
                'confirm' => 'Cần xác nhận model, tải trọng theo từng bán kính làm việc, chiều cao/tầm với, kích thước vận hành, nguồn điện và phương án an toàn của phiên bản được chọn.',
                'meta' => 'Cần cẩu chân nhện 1, 5, 8 và 12 tấn tại Phú Đức. Xem các cấu hình đang niêm yết và yêu cầu catalogue đúng phiên bản.',
            ],
            'cau-chan-nhen-chay-dien-1-5-tan' => [
                'intro' => 'là lựa chọn cẩu chân nhện chạy điện có tải trọng tối đa 1,5 tấn theo cấu hình đang niêm yết.',
                'context' => 'Sản phẩm dành cho khách hàng đang tìm một cấu hình cẩu chân nhện theo mức tải cụ thể, cần đối chiếu thêm không gian làm việc và yêu cầu lắp đặt.',
                'selection' => ['Đối chiếu tải trọng 1,5 tấn với công việc nâng thực tế.', 'Kiểm tra vị trí đặt cẩu, hướng vận hành và điều kiện cấp điện.', 'Xác nhận phạm vi cung cấp motor tời và các hạng mục đi kèm.'],
                'confirm' => 'Trước khi đặt hàng cần chốt model, bán kính làm việc, chiều cao/tầm với, điện áp, kích thước và hạng mục bàn giao của cấu hình cẩu.',
                'meta' => 'Cẩu chân nhện chạy điện tải trọng tối đa 1,5 tấn tại Phú Đức. Xem cấu hình, hạng mục đi kèm và yêu cầu catalogue trước khi chọn.',
            ],
            'cau-dien-thuy-luc-2-tan' => [
                'intro' => 'là cấu hình cẩu điện thủy lực tải trọng tối đa 2 tấn, cần 3 đốt, độ vươn 3 m và góc xoay 360° theo dữ liệu đang niêm yết.',
                'context' => 'Nội dung tập trung vào nhu cầu chọn cẩu theo tải, kiểu cần và vùng thao tác; các chỉ tiêu kỹ thuật ngoài phần được công bố cần được đối chiếu theo catalogue.',
                'selection' => ['So sánh tải trọng yêu cầu với mức tải tối đa 2 tấn được niêm yết.', 'Kiểm tra độ vươn 3 m và vùng xoay 360° có phù hợp vị trí thao tác.', 'Đối chiếu hạng mục chân cẩu, bình điện và motor tời trong báo giá.'],
                'confirm' => 'Cần xác nhận tải trọng theo bán kính, điện áp/dung lượng nguồn, kích thước lắp đặt, tốc độ vận hành và điều kiện an toàn trước khi chốt cấu hình.',
                'meta' => 'Cẩu điện thủy lực 2 tấn, cần 3 đốt, vươn 3 m, xoay 360° tại Phú Đức. Xem cấu hình và hạng mục đi kèm.',
            ],
            'cau-thuy-luc-500-kg' => [
                'intro' => 'là cấu hình cẩu thủy lực tải trọng 500 kg đang được Phú Đức niêm yết.',
                'context' => 'Trang phục vụ nhu cầu tìm cẩu thủy lực theo mức tải 500 kg và giúp khách hàng chuẩn bị các thông tin cần đối chiếu trước khi chọn thiết bị.',
                'selection' => ['Đối chiếu tải nâng thực tế với mức 500 kg của cấu hình.', 'Xác định vị trí lắp đặt, không gian thao tác và yêu cầu nguồn động lực.', 'Kiểm tra hạng mục motor đi kèm trong báo giá.'],
                'confirm' => 'Cần xác nhận model, bán kính làm việc, chiều cao/tầm với, kích thước, nguồn động lực và điều kiện tải trước khi đặt hàng.',
                'meta' => 'Cẩu thủy lực tải trọng 500 kg tại Phú Đức. Xem cấu hình đang niêm yết, motor đi kèm và các thông số cần xác nhận.',
            ],
            'cau-truc-chay-dien-co-chan-di-chuyen' => [
                'intro' => 'là dòng cẩu trục chạy điện có chân di chuyển, với các mức tải 2 tấn, 3 tấn và 5 tấn; mỗi cấu hình đang ghi cao 3 m, ngang 3 m.',
                'context' => 'Đây là trang để so sánh ba mức tải khi khách hàng cần cẩu trục có chân di chuyển, thay vì gộp nhu cầu này với cẩu trục cố định.',
                'selection' => ['Chọn mức tải 2, 3 hoặc 5 tấn theo tải nâng thực tế.', 'Đối chiếu kích thước cao 3 m, ngang 3 m với khu vực sử dụng.', 'Kiểm tra hành trình di chuyển, nguồn điện và hạng mục bàn giao theo catalogue.'],
                'confirm' => 'Cần xác nhận model, khẩu độ/chiều cao làm việc, hành trình, tốc độ, nguồn điện, tải trọng theo điều kiện vận hành và yêu cầu nền xưởng.',
                'meta' => 'Cẩu trục chạy điện có chân di chuyển tải 2, 3 và 5 tấn tại Phú Đức. Cấu hình đang ghi cao 3 m, ngang 3 m.',
            ],
            'cau-truc-co-dinh-xoay-360-do-chay-dien' => [
                'intro' => 'là cấu hình cẩu trục cố định chạy điện có khả năng xoay 360° theo tên sản phẩm, với lựa chọn tải 1 tấn và 2 tấn.',
                'context' => 'Trang dành cho khách hàng tìm cẩu trục cố định theo mức tải và yêu cầu xoay, khác với nhu cầu sử dụng cẩu trục có chân di chuyển.',
                'selection' => ['Đối chiếu lựa chọn tải 1 tấn hoặc 2 tấn với nhu cầu nâng.', 'Xác định vị trí cố định, vùng xoay và không gian thao tác.', 'Yêu cầu catalogue để kiểm tra cách lắp đặt và cấu hình nguồn điện.'],
                'confirm' => 'Cần xác nhận model, bán kính/vùng xoay, chiều cao/tầm với, nguồn điện, phương thức lắp đặt và tải trọng theo điều kiện làm việc.',
                'meta' => 'Cẩu trục cố định xoay 360° chạy điện tải 1 tấn và 2 tấn tại Phú Đức. Liên hệ để xác nhận cấu hình và lắp đặt.',
            ],
            'dieu-hoa-24v-cho-xe-tai-cat-noc' => [
                'intro' => 'là lựa chọn điều hòa 24V cho xe tải cắt nóc, đang có phiên bản 1 quạt gió, 2 quạt gió và cục nóng riêng.',
                'context' => 'Khách hàng có thể dùng trang này để đối chiếu kiểu bố trí điều hòa trước khi kiểm tra độ tương thích với xe tải và vị trí lắp đặt thực tế.',
                'selection' => ['Xác định xe sử dụng hệ điện 24V và vị trí dự kiến lắp đặt.', 'So sánh lựa chọn 1 quạt gió, 2 quạt gió hoặc cục nóng riêng.', 'Đối chiếu kích thước khoét nóc, dây dẫn và phụ kiện lắp đặt.'],
                'confirm' => 'Cần xác nhận điện áp thực tế của xe, kích thước lắp đặt, công suất làm mát, dòng tiêu thụ, phương án thoát nước và phụ kiện phù hợp với từng xe.',
                'meta' => 'Điều hòa 24V cho xe tải cắt nóc tại Phú Đức: lựa chọn 1 quạt, 2 quạt hoặc cục nóng riêng. Xác nhận tương thích trước khi lắp.',
            ],
            'may-bom-be-tong-chay-dau' => [
                'intro' => 'là trang thông tin cho máy bơm bê tông chạy dầu đang được Phú Đức niêm yết.',
                'context' => 'Sản phẩm phù hợp để khách hàng bắt đầu trao đổi về nhu cầu bơm bê tông, sau đó chốt đúng model theo vật liệu, khối lượng công việc và điều kiện thi công.',
                'selection' => ['Mô tả loại vật liệu, yêu cầu cấp liệu và quy mô công việc.', 'Xác định vị trí vận hành, phương án di chuyển và không gian đặt máy.', 'Yêu cầu catalogue để đối chiếu cấu hình bơm và hạng mục bàn giao.'],
                'confirm' => 'Cần xác nhận model, công suất động cơ, lưu lượng/áp lực, cỡ vật liệu phù hợp, kích thước, trọng lượng, đường ống và quy trình vệ sinh/bảo trì.',
                'meta' => 'Máy bơm bê tông chạy dầu tại Phú Đức. Liên hệ để nhận catalogue, xác nhận cấu hình bơm và thông số phù hợp công trình.',
            ],
            'may-cat-dieu-khien-tu-xa' => [
                'intro' => 'là lựa chọn máy cắt điều khiển từ xa đang được niêm yết tại Phú Đức.',
                'context' => 'Trang giúp khách hàng mô tả rõ vật liệu, kiểu cắt và điều kiện làm việc để Phú Đức đối chiếu model phù hợp thay vì suy đoán tính năng từ tên sản phẩm.',
                'selection' => ['Nêu rõ vật liệu, kích thước và yêu cầu cắt của công việc.', 'Kiểm tra môi trường vận hành và không gian thao tác an toàn.', 'Xác nhận phạm vi điều khiển, cấu hình lưỡi/cụm cắt và thời gian cung ứng.'],
                'confirm' => 'Cần xác nhận model, loại vật liệu xử lý, năng suất, nguồn động lực, phạm vi điều khiển, kích thước, phụ kiện và thời gian giao theo đơn hàng thực tế.',
                'meta' => 'Máy cắt điều khiển từ xa tại Phú Đức. Gửi yêu cầu vật liệu và công việc để xác nhận model, cấu hình điều khiển và thời gian cung ứng.',
            ],
            'may-gap-hang-khi-nen-100-kg' => [
                'intro' => 'là máy gắp hàng khí nén có tải trọng gắp 100 kg theo cấu hình đang niêm yết.',
                'context' => 'Nội dung phù hợp với khách hàng đang đánh giá giải pháp gắp/hỗ trợ nâng hạ theo mức tải 100 kg và cần chốt chi tiết đầu gắp trước khi đặt hàng.',
                'selection' => ['Đối chiếu khối lượng kiện hàng với tải trọng gắp 100 kg.', 'Mô tả hình dáng, bề mặt và cách đặt hàng để chọn đầu gắp.', 'Kiểm tra nguồn khí nén sẵn có tại vị trí sử dụng.'],
                'confirm' => 'Cần xác nhận áp suất/lưu lượng khí, kiểu đầu gắp, hành trình, kích thước, tốc độ, phương án lắp đặt và giới hạn tải cho đúng loại hàng.',
                'meta' => 'Máy gắp hàng khí nén tải trọng 100 kg tại Phú Đức. Liên hệ để chọn đầu gắp, kiểm tra nguồn khí nén và cấu hình phù hợp.',
            ],
            'may-phat-dien-nap-cho-xe-tai-chay-xang-7l' => [
                'intro' => 'là máy phát điện nạp cho xe tải chạy xăng, với dung tích 7L và các ghi chú về tự ngắt khi đầy, tự nổ khi hết điện và Bluetooth theo listing.',
                'context' => 'Trang phù hợp để trao đổi về nhu cầu cấp/nạp điện cho xe tải; các chỉ tiêu đầu ra và khả năng tương thích cần được xác nhận trên đúng cấu hình.',
                'selection' => ['Xác định hệ thống điện, kiểu xe và nhu cầu nạp/cấp điện.', 'Đối chiếu vị trí lắp đặt, kết nối và cách vận hành mong muốn.', 'Kiểm tra từng chức năng tự động/Bluetooth có áp dụng cho phiên bản báo giá.'],
                'confirm' => 'Cần xác nhận model, công suất/điện áp đầu ra, dung tích nhiên liệu, phương thức kết nối, kích thước, mức tiêu hao và điều kiện lắp đặt trên xe.',
                'meta' => 'Máy phát điện nạp cho xe tải chạy xăng 7L tại Phú Đức. Xác nhận công suất đầu ra, tương thích xe và chức năng theo đúng cấu hình.',
            ],
            'may-phun-vua-tuong-chay-dien' => [
                'intro' => 'là máy phun vữa tường chạy điện đang được Phú Đức niêm yết.',
                'context' => 'Trang là điểm khởi đầu để khách hàng trao đổi về loại vữa, phạm vi thi công và yêu cầu vận hành trước khi lựa chọn model phù hợp.',
                'selection' => ['Mô tả loại vữa, bề mặt thi công và quy mô công việc.', 'Xác định nguồn điện, vị trí đặt máy và quãng đường cấp vật liệu.', 'Yêu cầu catalogue để đối chiếu bộ phụ kiện và hướng dẫn vệ sinh.'],
                'confirm' => 'Cần xác nhận model, công suất, lưu lượng, loại vữa tương thích, chiều dài ống, nguồn điện, kích thước, phụ kiện và quy trình vệ sinh sau sử dụng.',
                'meta' => 'Máy phun vữa tường chạy điện tại Phú Đức. Liên hệ để xác nhận loại vữa phù hợp, lưu lượng, nguồn điện và cấu hình máy.',
            ],
            'may-xuc-lat-3-banh-gau-0-2-m3' => [
                'intro' => 'là máy xúc lật 3 bánh với gầu 0,2 m³ theo cấu hình đang niêm yết.',
                'context' => 'Nội dung dành cho khách hàng tìm máy xúc lật theo dung tích gầu 0,2 m³ và cần đánh giá thêm vật liệu, mặt bằng và phương án vận hành.',
                'selection' => ['Đối chiếu dung tích gầu 0,2 m³ với loại vật liệu và khối lượng công việc.', 'Khảo sát lối đi, nền làm việc và không gian quay đầu.', 'Yêu cầu catalogue để kiểm tra nguồn động lực và kích thước máy.'],
                'confirm' => 'Cần xác nhận model, tải vận hành, công suất/nguồn động lực, kích thước, trọng lượng, chiều cao đổ tải, lốp và điều kiện sử dụng.',
                'meta' => 'Máy xúc lật 3 bánh gầu 0,2 m³ tại Phú Đức. Gửi nhu cầu vật liệu và mặt bằng để xác nhận model, kích thước và cấu hình phù hợp.',
            ],
            'may-xuc-lat-4-banh-gau-0-4-m3' => [
                'intro' => 'là máy xúc lật 4 bánh có dung tích gầu 0,4 m³, với hai lựa chọn nguồn động lực chạy điện hoặc chạy dầu diesel đang niêm yết.',
                'context' => 'Trang giúp so sánh hai cấu hình cùng dung tích gầu 0,4 m³, đồng thời giữ các thông số vận hành khác ở trạng thái cần catalogue xác nhận.',
                'selection' => ['So sánh phương án chạy điện và chạy dầu diesel theo điều kiện sử dụng.', 'Đối chiếu dung tích gầu 0,4 m³ với vật liệu và sản lượng dự kiến.', 'Khảo sát lối đi, nền làm việc, khu vực quay đầu và phương án bảo dưỡng.'],
                'confirm' => 'Cần xác nhận model, tải vận hành, công suất/nguồn động lực, kích thước, trọng lượng, chiều cao đổ tải, lốp và cấu hình an toàn của từng phiên bản.',
                'meta' => 'Máy xúc lật 4 bánh gầu 0,4 m³ tại Phú Đức, lựa chọn chạy điện hoặc dầu diesel. Xem cấu hình và yêu cầu catalogue.',
            ],
            'may-xuc-lat-gau-0-7-m3' => [
                'intro' => 'là máy xúc lật có gầu 0,7 m³ theo cấu hình đang niêm yết.',
                'context' => 'Trang dành cho khách hàng tìm máy xúc lật theo dung tích gầu 0,7 m³ và cần đối chiếu thực tế loại vật liệu, mặt bằng cùng yêu cầu vận hành.',
                'selection' => ['Đối chiếu dung tích gầu 0,7 m³ với vật liệu và sản lượng công việc.', 'Khảo sát khu vực vận hành, lối đi và vị trí đổ tải.', 'Yêu cầu catalogue để kiểm tra nguồn động lực và kích thước.'],
                'confirm' => 'Cần xác nhận model, tải vận hành, công suất/nguồn động lực, kích thước, trọng lượng, chiều cao đổ tải, lốp và phương án bảo trì.',
                'meta' => 'Máy xúc lật gầu 0,7 m³ tại Phú Đức. Liên hệ để nhận catalogue và đối chiếu cấu hình theo vật liệu, mặt bằng, nhu cầu vận hành.',
            ],
            'motor-toi' => [
                'intro' => 'là trang so sánh motor tời với hai lựa chọn lực kéo 6.000 lbs và 12.000 lbs đang niêm yết.',
                'context' => 'Khách hàng có thể dùng trang này để sàng lọc theo mức lực kéo, sau đó chốt đúng motor tời dựa trên tải, vị trí lắp và điều kiện sử dụng.',
                'selection' => ['Xác định tải kéo thực tế và điều kiện sử dụng của tời.', 'So sánh lựa chọn 6.000 lbs và 12.000 lbs với nhu cầu.', 'Kiểm tra nguồn điện, dây cáp, điều khiển và phụ kiện lắp đặt.'],
                'confirm' => 'Cần xác nhận model, lực kéo theo lớp cáp, nguồn điện, chiều dài/cỡ cáp, tốc độ, kiểu điều khiển, kích thước và bộ phụ kiện đi kèm.',
                'meta' => 'Motor tời 6.000 lbs và 12.000 lbs tại Phú Đức. Liên hệ để đối chiếu lực kéo, nguồn điện, cáp và phụ kiện cho đúng nhu cầu.',
            ],
            'nha-container' => [
                'intro' => 'là lựa chọn nhà container với kích thước 20 feet và 40 feet đang được niêm yết.',
                'context' => 'Trang giúp khách hàng bắt đầu so sánh không gian theo hai kích thước trước khi xác định công năng, vị trí lắp đặt và hạng mục hoàn thiện.',
                'selection' => ['Chọn phương án 20 feet hoặc 40 feet theo không gian sử dụng.', 'Xác định mục đích bố trí bên trong và vị trí đặt container.', 'Đối chiếu phương án vận chuyển, nền móng, điện nước và hạng mục hoàn thiện.'],
                'confirm' => 'Cần xác nhận kích thước thực tế, vật liệu hoàn thiện, bố trí cửa/sổ, hệ điện nước, điều kiện vận chuyển/cẩu hạ và hạng mục bàn giao.',
                'meta' => 'Nhà container 20 feet và 40 feet tại Phú Đức. Liên hệ để xác nhận bố trí, vật liệu hoàn thiện, vận chuyển và phương án lắp đặt.',
            ],
            'xe-cho-hang-3-banh-1-tan' => [
                'intro' => 'là xe chở hàng 3 bánh tải 1 tấn với hai lựa chọn chạy điện và chạy dầu đang được niêm yết.',
                'context' => 'Trang phù hợp khi khách hàng cần so sánh hai phương án nguồn động lực cho nhu cầu chở hàng theo mức tải 1 tấn.',
                'selection' => ['Đối chiếu tải 1 tấn với hàng hóa và tuyến vận chuyển thực tế.', 'So sánh lựa chọn chạy điện hoặc chạy dầu theo điều kiện vận hành.', 'Khảo sát lối đi, bề mặt di chuyển và yêu cầu thùng/sàn chở hàng.'],
                'confirm' => 'Cần xác nhận model, công suất/nguồn động lực, quãng đường hoặc tiêu hao, kích thước thùng, kích thước xe, tốc độ, phanh và điều kiện tải.',
                'meta' => 'Xe chở hàng 3 bánh tải 1 tấn tại Phú Đức, lựa chọn chạy điện hoặc chạy dầu. Liên hệ để xác nhận cấu hình theo tuyến vận chuyển.',
            ],
            'xe-cho-hang-chay-dien' => [
                'intro' => 'là dòng xe chở hàng chạy điện có hai mức tải 500 kg và 700 kg theo cấu hình đang niêm yết.',
                'context' => 'Nội dung tập trung vào việc chọn mức tải phù hợp cho vận chuyển nội bộ, trong khi các dữ liệu về pin, quãng đường và kích thước cần được xác nhận theo phiên bản.',
                'selection' => ['So sánh tải 500 kg và 700 kg với hàng hóa mỗi chuyến.', 'Mô tả quãng đường, tần suất vận hành và điều kiện sạc.', 'Khảo sát lối đi, nền di chuyển và yêu cầu sàn/thùng chở hàng.'],
                'confirm' => 'Cần xác nhận model, pin/bộ sạc, thời gian vận hành/sạc, công suất, kích thước, bán kính quay, tốc độ, phanh và tải theo điều kiện sử dụng.',
                'meta' => 'Xe chở hàng chạy điện tải 500 kg và 700 kg tại Phú Đức. Xem cấu hình, gửi nhu cầu tuyến vận hành để xác nhận thông số phù hợp.',
            ],
            'xe-nang-dien-0-8-tan' => [
                'intro' => 'là xe nâng điện tải trọng 0,8 tấn theo cấu hình đang niêm yết.',
                'context' => 'Trang phục vụ nhu cầu tìm xe nâng điện 800 kg theo mức tải cụ thể; các yếu tố về chiều cao nâng, khung nâng và pin cần được đối chiếu trước khi đặt hàng.',
                'selection' => ['Đối chiếu tải 0,8 tấn với khối lượng pallet/hàng hóa thực tế.', 'Xác định chiều cao nâng, lối đi và bán kính quay cần đáp ứng.', 'Kiểm tra nguồn sạc, tần suất làm việc và môi trường kho/xưởng.'],
                'confirm' => 'Cần xác nhận model, tải tâm, chiều cao/loại khung nâng, pin/bộ sạc, kích thước, bán kính quay, lốp, tốc độ và thiết bị an toàn.',
                'meta' => 'Xe nâng điện 0,8 tấn (800 kg) tại Phú Đức. Liên hệ để xác nhận chiều cao nâng, pin, kích thước và cấu hình phù hợp kho xưởng.',
            ],
            'xe-nang-dien' => [
                'intro' => 'là trang so sánh xe nâng điện theo các mức tải 1 tấn, 1,5 tấn, 2,5 tấn và 3,5 tấn đang niêm yết.',
                'context' => 'Đây là trang hub để khách hàng thu hẹp lựa chọn theo tải trọng trước khi đối chiếu chiều cao nâng, tải tâm, pin và không gian vận hành của từng model.',
                'selection' => ['Chọn mức tải 1, 1,5, 2,5 hoặc 3,5 tấn theo hàng hóa thực tế.', 'Xác định chiều cao nâng, tải tâm và chiều rộng lối đi cần đáp ứng.', 'Kiểm tra tần suất vận hành, vị trí sạc và yêu cầu phụ kiện kho.'],
                'confirm' => 'Cần xác nhận model của từng mức tải, chiều cao/loại khung nâng, tải tâm, pin/bộ sạc, kích thước, bán kính quay, lốp, tốc độ và bảo hành.',
                'meta' => 'Xe nâng điện 1 tấn, 1,5 tấn, 2,5 tấn và 3,5 tấn tại Phú Đức. So sánh cấu hình và xác nhận thông số theo nhu cầu kho xưởng.',
            ],
            'xe-nang-dau-diesel-3-tan' => [
                'intro' => 'là xe nâng dầu diesel tải trọng 3 tấn theo cấu hình đang niêm yết.',
                'context' => 'Trang dành cho khách hàng cần xe nâng diesel theo mức tải 3 tấn và cần xác định đúng model theo hàng hóa, môi trường vận hành cùng chiều cao nâng.',
                'selection' => ['Đối chiếu tải 3 tấn với pallet/hàng hóa và tải tâm thực tế.', 'Xác định chiều cao nâng, lối đi và bề mặt vận hành.', 'Kiểm tra yêu cầu về nhiên liệu, khí thải, lốp và bảo trì tại nơi sử dụng.'],
                'confirm' => 'Cần xác nhận model, động cơ/công suất, chiều cao/loại khung nâng, tải tâm, kích thước, trọng lượng, lốp, mức tiêu hao và thiết bị an toàn.',
                'meta' => 'Xe nâng dầu diesel 3 tấn tại Phú Đức. Gửi nhu cầu tải, chiều cao nâng và môi trường làm việc để nhận catalogue đúng cấu hình.',
            ],
            'xe-nang-dien-500-kg-48v' => [
                'intro' => 'là xe nâng điện tải 500 kg, điện áp 48V theo cấu hình đang niêm yết.',
                'context' => 'Trang phục vụ truy vấn cần đồng thời mức tải 500 kg và hệ điện 48V; các thông số pin, bộ sạc và chiều cao nâng cần được chốt theo model.',
                'selection' => ['Đối chiếu tải 500 kg với hàng hóa và tải tâm dự kiến.', 'Xác định chiều cao nâng, lối đi và không gian quay đầu.', 'Kiểm tra hệ điện 48V, vị trí sạc và thời gian vận hành cần thiết.'],
                'confirm' => 'Cần xác nhận model, loại pin/bộ sạc, chiều cao/loại khung nâng, tải tâm, kích thước, bán kính quay, tốc độ và phụ kiện an toàn.',
                'meta' => 'Xe nâng điện 500 kg 48V tại Phú Đức. Liên hệ để xác nhận pin, bộ sạc, chiều cao nâng và kích thước theo nhu cầu vận hành.',
            ],
            'xe-nang-ket-hop-cho-hang-1-5-tan' => [
                'intro' => 'là xe nâng kết hợp chở hàng có tải trọng 1,5 tấn theo cấu hình đang niêm yết.',
                'context' => 'Trang giúp khách hàng làm rõ nhu cầu kết hợp nâng và chở hàng trước khi lựa chọn model, vì các chi tiết về cơ cấu làm việc cần được xác nhận trong catalogue.',
                'selection' => ['Mô tả riêng nhu cầu nâng, chở và tải trọng của từng công việc.', 'Khảo sát lối đi, mặt bằng và khu vực nhận/trả hàng.', 'Yêu cầu hình ảnh/catalouge đúng phiên bản để kiểm tra bố trí thiết bị.'],
                'confirm' => 'Cần xác nhận model, chức năng nâng/chở, tải theo từng chức năng, chiều cao nâng, nguồn động lực, kích thước, bán kính quay, phanh và phụ kiện.',
                'meta' => 'Xe nâng kết hợp chở hàng tải 1,5 tấn tại Phú Đức. Gửi nhu cầu nâng và vận chuyển để xác nhận model, cấu hình và thông số thực tế.',
            ],
            'xe-nang-pallet-2-tan' => [
                'intro' => 'là xe nâng pallet tải nâng 2 tấn, gồm lựa chọn tiêu chuẩn và phiên bản có cân điện tử theo dữ liệu đang niêm yết.',
                'context' => 'Trang hỗ trợ khách hàng so sánh hai lựa chọn 2 tấn, đặc biệt khi cần xem xét chức năng cân điện tử mà không suy đoán thông số độ chính xác.',
                'selection' => ['Đối chiếu tải nâng 2 tấn với khối lượng pallet thực tế.', 'Xác định có cần phiên bản kèm cân điện tử hay không.', 'Kiểm tra kích thước pallet, lối đi, loại bánh và bề mặt kho.'],
                'confirm' => 'Cần xác nhận model, chiều dài/rộng càng, chiều cao nâng/hạ, loại bánh, trọng lượng, phương thức vận hành và thông số của cân điện tử nếu chọn phiên bản này.',
                'meta' => 'Xe nâng pallet tải 2 tấn tại Phú Đức, có lựa chọn tiêu chuẩn và kèm cân điện tử. Xác nhận kích thước càng, chiều cao nâng trước khi mua.',
            ],
            'xe-nang-tay' => [
                'intro' => 'là xe nâng tay có hai cấu hình: tải 200 kg nâng cao 1 m và tải 260 kg nâng cao 1,2 m.',
                'context' => 'Trang giúp khách hàng so sánh đồng thời tải trọng và chiều cao nâng trước khi chọn xe nâng tay phù hợp với vị trí lấy/đặt hàng.',
                'selection' => ['Đối chiếu lựa chọn 200 kg/1 m và 260 kg/1,2 m với hàng hóa.', 'Xác định chiều cao cần lấy hoặc đặt hàng.', 'Kiểm tra kích thước hàng, lối đi và bề mặt di chuyển.'],
                'confirm' => 'Cần xác nhận model, kích thước càng, chiều cao hạ, loại bánh, kích thước máy, trọng lượng, cách vận hành và điều kiện bảo hành.',
                'meta' => 'Xe nâng tay 200 kg nâng 1 m và 260 kg nâng 1,2 m tại Phú Đức. So sánh tải trọng, chiều cao nâng và chọn cấu hình phù hợp.',
            ],
            'xe-rua-banh-xich-cho-hang-dieu-khien-tu-xa' => [
                'intro' => 'là xe rùa bánh xích chở hàng điều khiển từ xa, có các cấu hình chạy điện tải 500 kg, 1 tấn và 2 tấn.',
                'context' => 'Trang được xây dựng cho nhu cầu so sánh xe rùa bánh xích theo ba mức tải; các thông số về pin, phạm vi điều khiển và kích thước cần được xác nhận theo từng phiên bản.',
                'selection' => ['So sánh tải 500 kg, 1 tấn và 2 tấn với khối lượng mỗi chuyến.', 'Mô tả bề mặt di chuyển, lối đi và điều kiện vận hành thực tế.', 'Xác định nhu cầu điều khiển từ xa, thời gian làm việc và vị trí sạc.'],
                'confirm' => 'Cần xác nhận model, pin/bộ sạc, phạm vi điều khiển, kích thước thùng/sàn, tốc độ, trọng lượng, loại bánh xích, điều kiện tải và bảo hành.',
                'meta' => 'Xe rùa bánh xích chở hàng điều khiển từ xa tải 500 kg, 1 tấn và 2 tấn tại Phú Đức. Xem cấu hình chạy điện và yêu cầu catalogue.',
            ],
            'xe-tai-banh-xich-cho-hang-1-8-tan' => [
                'intro' => 'là xe tải bánh xích chở hàng tải 1,8 tấn, với cấu hình chạy dầu diesel đang được niêm yết.',
                'context' => 'Trang phù hợp khi khách hàng cần xe chở hàng bánh xích theo mức tải 1,8 tấn; thuật ngữ “4 máy” trong listing gốc chưa được diễn giải thành thông số kỹ thuật.',
                'selection' => ['Đối chiếu tải 1,8 tấn với khối lượng và cách chất hàng thực tế.', 'Mô tả bề mặt di chuyển, lối đi, tuyến vận hành và điều kiện làm việc.', 'Yêu cầu catalogue để kiểm tra động cơ, kích thước thùng và cấu hình bánh xích.'],
                'confirm' => 'Cần xác nhận model, động cơ/công suất, kích thước thùng, tải theo điều kiện làm việc, tốc độ, kích thước xe, trọng lượng, kiểu bánh xích và bảo hành.',
                'meta' => 'Xe tải bánh xích chở hàng 1,8 tấn chạy dầu diesel tại Phú Đức. Liên hệ để nhận catalogue và xác nhận cấu hình chi tiết.',
            ],
        ];

        return $profiles[$slug] ?? [
            'intro' => 'được Phú Đức cung cấp theo cấu hình đang niêm yết.',
            'context' => 'Trang giúp khách hàng đối chiếu nhu cầu sử dụng trước khi yêu cầu catalogue và báo giá.',
            'selection' => ['Xác định nhu cầu sử dụng và tải trọng/công việc thực tế.', 'Đối chiếu điều kiện vận hành, không gian và nguồn cấp.', 'Yêu cầu catalogue của đúng phiên bản trước khi chọn.'],
            'confirm' => 'Cần xác nhận model, thông số kỹ thuật, kích thước, hạng mục đi kèm và điều kiện bảo hành của phiên bản báo giá.',
            'meta' => 'Thông tin sản phẩm tại Phú Đức. Xem cấu hình đang niêm yết và liên hệ để xác nhận thông số phù hợp.',
        ];
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

    private function metaDescription(string $slug): string
    {
        return Str::limit($this->contentProfile($slug)['meta'], 160, '');
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
