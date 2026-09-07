<?php

namespace Database\Seeders;

use App\Models\MediaLibrary;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class PhuDucSeoProductSeeder extends Seeder
{
    private const SOURCE_DIRECTORY = 'docs/seo/alibaba/search-images';

    /**
     * Seed the five product pages prepared for the Phú Đức SEO catalogue.
     *
     * This seeder is deliberately not registered in DatabaseSeeder: a production
     * release must invoke it explicitly after the content owner has approved it.
     */
    public function run(): void
    {
        $products = $this->products();
        $this->assertSourceImagesExist($products);

        foreach ($products as $productData) {
            DB::transaction(function () use ($productData): void {
                $product = $this->upsertProduct($productData);
                $this->syncImages($product, $productData['images']);
            });
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function upsertProduct(array $data): Product
    {
        $bySku = Product::query()->where('sku', $data['sku'])->first();
        $bySlug = Product::query()->where('slug', $data['slug'])->first();

        if ($bySku && $bySlug && $bySku->isNot($bySlug)) {
            throw new RuntimeException(sprintf(
                'SKU %s và slug %s đang thuộc hai sản phẩm khác nhau.',
                $data['sku'],
                $data['slug'],
            ));
        }

        $product = $bySku ?? $bySlug ?? new Product;
        $product->forceFill([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'description' => $data['description'].$this->relatedLinks($data['slug']),
            'price' => 0,
            'sku' => $data['sku'],
            'stock' => 0,
            'specifications' => $data['specifications'],
            'status' => 'active',
            'meta_title' => $data['meta_title'],
            'meta_description' => $data['meta_description'],
        ])->save();

        return $product;
    }

    /**
     * @param  list<array{source: string, alt: string, caption: string}>  $images
     */
    private function syncImages(Product $product, array $images): void
    {
        foreach ($images as $index => $image) {
            $source = $this->sourcePath($image['source']);
            $filename = sprintf('%02d-%s', $index + 1, basename($image['source']));
            $mediaPath = 'media/seo-products/'.$product->slug.'/'.$filename;
            $productPath = 'products/'.$product->id.'/seo/'.$filename;
            $contents = File::get($source);

            if ($contents === '') {
                throw new RuntimeException('Không thể đọc ảnh nguồn: '.$source);
            }

            Storage::disk('public')->put($mediaPath, $contents);
            Storage::disk('public')->put($productPath, $contents);

            MediaLibrary::query()->updateOrCreate(
                ['file_path' => $mediaPath],
                [
                    'file_name' => $filename,
                    'mime_type' => 'image/webp',
                    'size' => strlen($contents),
                    'alt_text' => $image['alt'],
                    'caption' => $image['caption'],
                ],
            );

            $product->images()->updateOrCreate(
                ['image_path' => $productPath],
                [
                    'alt_text' => $image['alt'],
                    'is_360' => false,
                    'sort_order' => $index,
                ],
            );
        }
    }

    /**
     * @param  list<array<string, mixed>>  $products
     */
    private function assertSourceImagesExist(array $products): void
    {
        $missing = [];

        foreach ($products as $product) {
            foreach ($product['images'] as $image) {
                $path = $this->sourcePath($image['source']);
                if (! is_file($path) || filesize($path) === 0) {
                    $missing[] = $path;
                }
            }
        }

        if ($missing !== []) {
            throw new RuntimeException('Thiếu ảnh nguồn để seed: '.implode(', ', $missing));
        }
    }

    private function sourcePath(string $relativePath): string
    {
        return base_path(self::SOURCE_DIRECTORY.DIRECTORY_SEPARATOR.$relativePath);
    }

    private function relatedLinks(string $currentSlug): string
    {
        $products = [
            'cau-dien-thuy-luc-1-tan-xoay-360-do' => 'Cẩu điện thủy lực 1 tấn xoay 360°',
            'can-cau-chan-nhen-banh-xich-5-tan' => 'Cần cẩu chân nhện bánh xích 5 tấn',
            'cau-truc-chay-dien-co-chan-di-chuyen' => 'Cẩu trục chạy điện có chân di chuyển',
            'may-xuc-lat-mini-banh-lop-diesel-904' => 'Máy xúc lật mini bánh lốp diesel 904',
            'xe-nang-dien-doi-trong-4-banh' => 'Xe nâng điện đối trọng 4 bánh',
        ];

        $items = [];
        foreach ($products as $slug => $name) {
            if ($slug !== $currentSlug) {
                $items[] = sprintf('<li><a href="/san-pham/%s">%s</a></li>', $slug, $name);
            }
        }

        return '<h2>Thiết bị liên quan</h2><ul>'.implode('', $items).'</ul>';
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function products(): array
    {
        return [
            [
                'sku' => 'PD-CRANE-HYD-1000',
                'name' => 'Cẩu điện thủy lực 1 tấn xoay 360°',
                'slug' => 'cau-dien-thuy-luc-1-tan-xoay-360-do',
                'meta_title' => 'Cẩu điện thủy lực 1 tấn xoay 360° | Phú Đức',
                'meta_description' => 'Cẩu điện thủy lực 1 tấn xoay 360°, cần ống lồng 750 mm, nâng hạ bằng xi lanh thủy lực; phù hợp lắp xe tải nhẹ và điểm nâng hạ cố định.',
                'description' => <<<'HTML'
<p>Cẩu điện thủy lực 1 tấn xoay 360° là giải pháp nâng hạ gọn nhẹ cho xe tải nhỏ, xe bán tải, xe ba bánh hoặc vị trí lắp cố định. Thiết bị dùng xi lanh thủy lực để nâng hạ cần, hỗ trợ chọn nguồn 12VDC, 24VDC hoặc 220VAC theo phương án lắp đặt.</p>
<h2>Khả năng làm việc</h2>
<p>Cần có góc nâng 0–140°, kết cấu ống lồng vươn 750 mm và cơ cấu xoay 360°. Tải trọng tối đa 1.000 kg áp dụng tại tầm với phù hợp; tải trọng thực tế giảm theo độ vươn cần và phải tuân theo biểu đồ tải của cấu hình chốt khi báo giá.</p>
<h2>Ứng dụng phù hợp</h2>
<p>Phù hợp nâng máy móc, thiết bị, vật tư và hàng hóa có tải trọng nhỏ tại xưởng, công trường, kho hoặc trên phương tiện vận chuyển. Phú Đức tư vấn nguồn điện, kiểu xoay, tời điện và phương án gá lắp theo xe hoặc vị trí sử dụng.</p>
HTML,
                'specifications' => [
                    $this->spec('Tải trọng nâng tối đa', '1.000 kg tại tầm với phù hợp; tải giảm theo độ vươn cần'),
                    $this->spec('Góc nâng cần', '0–140°'),
                    $this->spec('Góc xoay', '360°'),
                    $this->spec('Nguồn điện tùy chọn', '12VDC / 24VDC / 220VAC'),
                    $this->spec('Cơ cấu nâng hạ', 'Xi lanh thủy lực; điều khiển tay hoặc điều khiển từ xa tùy cấu hình'),
                    $this->spec('Kiểu xoay', 'Xoay tay hoặc motor thủy lực tùy cấu hình'),
                    $this->spec('Cần ống lồng', '2 đốt kéo tay hoặc 3 đốt tự động/điều khiển từ xa'),
                    $this->spec('Độ vươn ống lồng', '750 mm'),
                    $this->spec('Tời nâng', 'Tời điện tùy cấu hình'),
                    $this->spec('Khối lượng tham khảo', 'Khoảng 150 kg'),
                ],
                'images' => [
                    $this->image('cau-dien-thuy-luc-2-tan/01-primary.webp', 'Cẩu điện thủy lực 1 tấn xoay 360 độ lắp trên xe tải nhẹ', 'Cẩu điện thủy lực 1 tấn xoay 360°.'),
                    $this->image('cau-dien-thuy-luc-2-tan/02-angle.webp', 'Cần cẩu thủy lực 1 tấn với cần vươn dạng ống lồng', 'Cần ống lồng của cẩu điện thủy lực 1 tấn.'),
                    $this->image('cau-dien-thuy-luc-2-tan/03-detail.webp', 'Cụm tời điện và thân cẩu điện thủy lực 1 tấn', 'Chi tiết thân cẩu và cụm tời điện.'),
                ],
            ],
            [
                'sku' => 'PD-SPIDER-5T',
                'name' => 'Cần cẩu chân nhện bánh xích 5 tấn',
                'slug' => 'can-cau-chan-nhen-banh-xich-5-tan',
                'meta_title' => 'Cần cẩu chân nhện bánh xích 5 tấn | Phú Đức',
                'meta_description' => 'Cần cẩu chân nhện bánh xích 5 tấn, bốn chân chống thủy lực, cần ống lồng và xoay 360°; cơ động trong kho, công trường và khu vực có lối vào hẹp.',
                'description' => <<<'HTML'
<p>Cần cẩu chân nhện bánh xích 5 tấn là thiết bị nâng hạ cơ động cho khu vực có lối vào hẹp, nền đất phức tạp hoặc không gian làm việc giới hạn. Hệ bánh xích cao su kết hợp bốn chân chống thủy lực giúp máy ổn định khi thao tác.</p>
<h2>Khả năng làm việc</h2>
<p>Cấu hình tham chiếu 5 tấn dùng cần ống lồng, quay 360° và chân chống thủy lực. Chiều cao nâng tham khảo 16,8 m; tại bán kính 16 m, tải trọng tham khảo là 0,21 tấn. Mọi ca nâng phải chốt đúng biểu đồ tải, chiều cao, bán kính làm việc và điều kiện nền trước khi vận hành.</p>
<h2>Ứng dụng phù hợp</h2>
<p>Phù hợp lắp đặt kính, kết cấu thép, thiết bị MEP, máy móc và vật tư trong nhà xưởng, tầng hầm, khu đô thị hoặc công trường có không gian hạn chế.</p>
HTML,
                'specifications' => [
                    $this->spec('Tải trọng định mức', '5 tấn'),
                    $this->spec('Hệ di chuyển', 'Bánh xích cao su'),
                    $this->spec('Chân chống', '04 chân chống thủy lực'),
                    $this->spec('Kiểu cần', 'Cần ống lồng'),
                    $this->spec('Góc quay', '360°'),
                    $this->spec('Điều khiển', 'Tại máy hoặc điều khiển từ xa tùy cấu hình'),
                    $this->spec('Chiều cao nâng tham khảo', '16,8 m'),
                    $this->spec('Bán kính làm việc tham khảo', '16 m tại tải 0,21 tấn'),
                    $this->spec('Khối lượng máy tham khảo', 'Khoảng 6,55 tấn'),
                    $this->spec('Kích thước tham khảo (D × R × C)', '4.600 × 1.430 × 2.170 mm'),
                    $this->spec('Nguồn động lực', 'Diesel, điện lưới hoặc hybrid tùy cấu hình'),
                ],
                'images' => [
                    $this->image('can-cau-chan-nhen/01-primary.webp', 'Cần cẩu chân nhện bánh xích 5 tấn với bốn chân chống thủy lực', 'Cần cẩu chân nhện bánh xích 5 tấn.'),
                    $this->image('can-cau-chan-nhen/02-angle.webp', 'Cần cẩu chân nhện bánh xích thu gọn khi di chuyển', 'Cần cẩu chân nhện trong trạng thái thu gọn.'),
                    $this->image('can-cau-chan-nhen/03-detail.webp', 'Cần cẩu chân nhện 5 tấn làm việc với cần ống lồng', 'Cần ống lồng và chân chống thủy lực của cẩu chân nhện.'),
                ],
            ],
            [
                'sku' => 'PD-GANTRY-MOBILE',
                'name' => 'Cẩu trục chạy điện có chân di chuyển',
                'slug' => 'cau-truc-chay-dien-co-chan-di-chuyen',
                'meta_title' => 'Cẩu trục chạy điện có chân di chuyển | Phú Đức',
                'meta_description' => 'Cẩu trục chạy điện có chân di chuyển, tải trọng cấu hình 2–5 tấn, dùng palang điện và bánh xe có khóa; thiết kế theo khẩu độ, chiều cao nâng và mặt bằng xưởng.',
                'description' => <<<'HTML'
<p>Cẩu trục chạy điện có chân di chuyển là hệ cẩu cổng chữ A gọn nhẹ, sử dụng palang điện để nâng hạ và bánh xe để thay đổi vị trí làm việc trong xưởng, kho hoặc khu vực lắp ráp. Kết cấu được lựa chọn theo tải trọng, khẩu độ và chiều cao nâng thực tế.</p>
<h2>Khả năng làm việc</h2>
<p>Thiết bị có thể cấu hình tải trọng 2 tấn, 3 tấn hoặc 5 tấn; dùng palang xích điện hoặc palang cáp điện. Bánh xe có thể bổ sung khóa hoặc phanh. Khẩu độ, chiều cao nâng, điện áp và kiểu điều khiển được chốt theo bản vẽ kỹ thuật của công trình.</p>
<h2>Ứng dụng phù hợp</h2>
<p>Phù hợp lắp ráp máy, bảo trì thiết bị, bốc xếp khuôn, nâng hàng trong kho và các vị trí không bố trí cầu trục cố định.</p>
HTML,
                'specifications' => [
                    $this->spec('Tải trọng nâng', '2 tấn / 3 tấn / 5 tấn, chọn theo cấu hình'),
                    $this->spec('Kết cấu', 'Cổng chữ A hoặc dầm đơn, thiết kế theo tải trọng và nhịp'),
                    $this->spec('Cơ cấu nâng', 'Palang xích điện hoặc palang cáp điện'),
                    $this->spec('Cơ cấu di chuyển', 'Bánh xe di chuyển; khóa hoặc phanh tùy cấu hình'),
                    $this->spec('Điều khiển', 'Tay bấm treo hoặc điều khiển từ xa tùy cấu hình'),
                    $this->spec('Khẩu độ', 'Thiết kế theo mặt bằng và hành trình di chuyển'),
                    $this->spec('Chiều cao nâng', 'Thiết kế theo cao độ móc nâng và không gian làm việc'),
                    $this->spec('Nguồn điện', 'Theo palang và nguồn điện tại công trình'),
                    $this->spec('Yêu cầu báo giá', 'Cần xác nhận tải trọng, khẩu độ, chiều cao nâng, điện áp và điều kiện nền'),
                ],
                'images' => [
                    $this->image('cau-truc-chay-dien-co-chan-di-chuyen/01-primary.webp', 'Cẩu trục chạy điện có chân di chuyển và palang điện', 'Cẩu trục chạy điện có chân di chuyển.'),
                    $this->image('cau-truc-chay-dien-co-chan-di-chuyen/02-angle.webp', 'Khung cẩu trục di động dạng cổng chữ A trong xưởng', 'Khung cẩu trục dạng cổng chữ A.'),
                    $this->image('cau-truc-chay-dien-co-chan-di-chuyen/03-detail.webp', 'Palang điện trên dầm cẩu trục có chân di chuyển', 'Palang điện và dầm nâng của cẩu trục di động.'),
                ],
            ],
            [
                'sku' => 'PD-LOADER-904',
                'name' => 'Máy xúc lật mini bánh lốp diesel 904',
                'slug' => 'may-xuc-lat-mini-banh-lop-diesel-904',
                'meta_title' => 'Máy xúc lật mini bánh lốp diesel 904 | Phú Đức',
                'meta_description' => 'Máy xúc lật mini bánh lốp diesel 904, tải trọng định mức 400 kg, động cơ 18/22 HP, chiều cao đổ 1,6 m; phù hợp bốc xúc vật liệu và di chuyển trong không gian hẹp.',
                'description' => <<<'HTML'
<p>Máy xúc lật mini bánh lốp diesel 904 là thiết bị bốc xúc gọn nhẹ cho kho vật liệu, trang trại, công trình dân dụng và khu vực có lối đi hẹp. Hệ bốn bánh giúp máy cơ động khi vận chuyển vật liệu rời hoặc thao tác với gầu và bộ công tác tương thích.</p>
<h2>Khả năng làm việc</h2>
<p>Cấu hình 904 có tải trọng định mức 400 kg, lựa chọn động cơ diesel 18 HP hoặc 22 HP, chiều cao đổ 1,6 m và chiều cao nâng 2,3 m có thể điều chỉnh theo cấu hình. Dung tích gầu không được cố định trong nội dung này; sẽ được chốt theo gầu và vật liệu sử dụng.</p>
<h2>Ứng dụng phù hợp</h2>
<p>Phù hợp xúc cát, đất, đá mi, nông sản và vật liệu rời; đồng thời hỗ trợ san gạt, vận chuyển cự ly ngắn và vệ sinh mặt bằng.</p>
HTML,
                'specifications' => [
                    $this->spec('Tải trọng định mức', '400 kg'),
                    $this->spec('Động cơ diesel', '18 HP hoặc 22 HP'),
                    $this->spec('Chiều cao đổ', '1,6 m'),
                    $this->spec('Chiều cao nâng', '2,3 m, có thể điều chỉnh theo cấu hình'),
                    $this->spec('Kích thước (D × R × C)', '2,8 × 1,0 × 1,5 m'),
                    $this->spec('Trọng lượng máy', 'Khoảng 850 kg'),
                    $this->spec('Hệ di chuyển', 'Bốn bánh lốp'),
                    $this->spec('Dung tích gầu', 'Chọn theo gầu và vật liệu; xác nhận khi báo giá'),
                ],
                'images' => [
                    $this->image('may-xuc-lat-4-banh-gau-0-4-m3/01-primary.webp', 'Máy xúc lật mini bánh lốp diesel 904 tải trọng 400 kg', 'Máy xúc lật mini diesel 904.'),
                    $this->image('may-xuc-lat-4-banh-gau-0-4-m3/02-angle.webp', 'Máy xúc lật mini 904 nhìn từ góc nghiêng', 'Góc nghiêng của máy xúc lật mini 904.'),
                    $this->image('may-xuc-lat-4-banh-gau-0-4-m3/03-detail.webp', 'Gầu và hệ thống nâng của máy xúc lật mini diesel 904', 'Chi tiết gầu và hệ thống nâng của máy xúc lật mini 904.'),
                ],
            ],
            [
                'sku' => 'PD-FORKLIFT-ELECTRIC-4W',
                'name' => 'Xe nâng điện đối trọng 4 bánh 1,5–3,5 tấn',
                'slug' => 'xe-nang-dien-doi-trong-4-banh',
                'meta_title' => 'Xe nâng điện đối trọng 4 bánh 1,5–3,5 tấn | Phú Đức',
                'meta_description' => 'Xe nâng điện đối trọng 4 bánh Phú Đức, lựa chọn tải trọng 1,5 tấn hoặc 3,5 tấn, nâng tiêu chuẩn 3.000 mm; phù hợp kho bãi, nhà xưởng và logistics.',
                'description' => <<<'HTML'
<p>Xe nâng điện đối trọng 4 bánh là giải pháp di chuyển pallet và hàng hóa trong kho, nhà xưởng và trung tâm logistics. Dòng sản phẩm có các cấu hình tải trọng 1,5 tấn và 3,5 tấn; cần chọn đúng model, chiều cao nâng, pin/ắc quy và bộ công tác theo loại hàng.</p>
<h2>Khả năng làm việc</h2>
<p>Phiên bản CPD-15 có tải trọng 1.500 kg. Phiên bản HT-3.5T/CPD35 có tải trọng 3.500 kg, kích thước thân xe tham khảo 2.750 × 1.220 × 2.180 mm (không gồm càng), chiều dài cơ sở 1.860 mm và hệ lái thủy lực. Chiều cao nâng 3.000 mm là cấu hình phổ biến và có thể tùy chỉnh theo model.</p>
<h2>Ứng dụng phù hợp</h2>
<p>Phù hợp nâng pallet, hàng đóng kiện, vật tư sản xuất và hàng hóa trong không gian cần vận hành êm, không phát thải tại điểm sử dụng. Cần xác nhận loại pallet, lối đi, nền kho, chiều cao kệ và ca làm việc trước khi chọn xe.</p>
HTML,
                'specifications' => [
                    $this->spec('Kiểu xe', 'Xe nâng điện đối trọng 4 bánh'),
                    $this->spec('Tải trọng phiên bản CPD-15', '1.500 kg'),
                    $this->spec('Tải trọng phiên bản HT-3.5T / CPD35', '3.500 kg'),
                    $this->spec('Chiều cao nâng phổ biến', '3.000 mm; tùy chỉnh theo model'),
                    $this->spec('Kích thước HT-3.5T (D × R × C)', '2.750 × 1.220 × 2.180 mm, không gồm càng'),
                    $this->spec('Chiều dài cơ sở HT-3.5T', '1.860 mm'),
                    $this->spec('Tốc độ di chuyển HT-3.5T', '12 km/h không tải; 10 km/h có tải'),
                    $this->spec('Lốp HT-3.5T', 'Trước 28 × 9-15; sau 650-10'),
                    $this->spec('Hệ lái HT-3.5T', 'Trợ lực thủy lực'),
                    $this->spec('Nguồn động lực', 'Điện ắc quy; dung lượng chọn theo model và ca làm việc'),
                ],
                'images' => [
                    $this->image('xe-nang-dien/01-primary.webp', 'Xe nâng điện đối trọng 4 bánh dùng trong kho và nhà xưởng', 'Xe nâng điện đối trọng 4 bánh.'),
                    $this->image('xe-nang-dien/02-angle.webp', 'Xe nâng điện đối trọng 3,5 tấn nhìn từ góc nghiêng', 'Góc nghiêng xe nâng điện đối trọng 3,5 tấn.'),
                    $this->image('xe-nang-dien/03-detail.webp', 'Cụm càng nâng của xe nâng điện đối trọng 4 bánh', 'Càng nâng của xe nâng điện đối trọng.'),
                ],
            ],
        ];
    }

    /**
     * @return array{key: string, label: string, value: string}
     */
    private function spec(string $label, string $value): array
    {
        return [
            'key' => $label,
            'label' => $label,
            'value' => $value,
        ];
    }

    /**
     * @return array{source: string, alt: string, caption: string}
     */
    private function image(string $source, string $alt, string $caption): array
    {
        return compact('source', 'alt', 'caption');
    }
}
