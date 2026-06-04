<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class RealImageSeeder extends Seeder
{
    private const WIDTH = 1100;

    private array $productImages = [
        'PD-T2000' => [
            'file' => 'Goupil G6 Box Van - Electric utility vehicle, Seignosse, Aquitaine, France.jpg',
            'path' => 'demo/products/pd-t2000-electric-utility-vehicle.jpg',
        ],
        'PD-T3000' => [
            'file' => 'Goupil G7 Box Van - Electric utility vehicle, Seignosse, Aquitaine, France.jpg',
            'path' => 'demo/products/pd-t3000-electric-box-van.jpg',
        ],
        'PD-P14' => [
            'file' => 'The COMET Public Utility E-Vehicle.JPG',
            'path' => 'demo/products/pd-p14-electric-passenger-vehicle.jpg',
        ],
        'PD-P8' => [
            'file' => 'Electric Golf Cart (24909884890).jpg',
            'path' => 'demo/products/pd-p8-electric-passenger-cart.jpg',
        ],
        'PD-G4' => [
            'file' => 'Electric Golf cart EZGO 1.jpg',
            'path' => 'demo/products/pd-g4-electric-golf-cart.jpg',
        ],
        'PD-P4' => [
            'file' => 'Custom Electric Golf Cart Built by Sundance Golf Cars.jpg',
            'path' => 'demo/products/pd-p4-electric-mini-cart.jpg',
        ],
        'PD-F15' => [
            'file' => 'Carretilla elevadora electrica.jpg',
            'path' => 'demo/products/pd-f15-electric-forklift.jpg',
        ],
        'PD-TW5' => [
            'file' => 'University of Akron E-Ride Industries EXV2 Electric Utility Vehicle (50127401358).jpg',
            'path' => 'demo/products/pd-tw5-electric-tow-vehicle.jpg',
        ],
        'PD-S2' => [
            'file' => 'Zap-taxi-jonway-electric-suv-a380.jpg',
            'path' => 'demo/products/pd-s2-electric-patrol-vehicle.jpg',
        ],
        'PD-GC1000' => [
            'file' => 'BCS Alkè ATX280E.JPG',
            'path' => 'demo/products/pd-gc1000-electric-utility-truck.jpg',
        ],
    ];

    private array $postImages = [
        [
            'file' => 'Goupil G6 Box Van - Electric utility vehicle, Seignosse, Aquitaine, France.jpg',
            'path' => 'demo/posts/electric-industrial-vehicle-trend.jpg',
        ],
        [
            'file' => 'Carretilla elevadora electrica.jpg',
            'path' => 'demo/posts/electric-forklift-maintenance.jpg',
        ],
        [
            'file' => 'The COMET Public Utility E-Vehicle.JPG',
            'path' => 'demo/posts/electric-vehicle-expo.jpg',
        ],
    ];

    public function run(): void
    {
        Storage::disk('public')->makeDirectory('demo/products');
        Storage::disk('public')->makeDirectory('demo/posts');

        foreach ($this->productImages as $sku => $image) {
            sleep(1);

            if (! $this->ensureImage($image['file'], $image['path'])) {
                continue;
            }

            $product = Product::where('sku', $sku)->first();

            if (! $product) {
                continue;
            }

            $product->images()->updateOrCreate(
                ['image_path' => $image['path']],
                ['is_360' => false, 'sort_order' => 0],
            );
        }

        Post::where('status', 'published')
            ->orderBy('id')
            ->take(count($this->postImages))
            ->get()
            ->values()
            ->each(function (Post $post, int $index) {
                $image = $this->postImages[$index];

                sleep(1);

                if ($this->ensureImage($image['file'], $image['path'])) {
                    $post->update(['featured_image' => $image['path']]);
                }
            });
    }

    private function ensureImage(string $fileName, string $path): bool
    {
        if (Storage::disk('public')->exists($path)) {
            return true;
        }

        $url = $this->resolveCommonsImageUrl($fileName);

        if (! $url) {
            $this->command?->warn("Skip image {$fileName}: cannot resolve Wikimedia URL");

            return false;
        }

        try {
            $response = Http::timeout(35)
                ->retry(2, 500)
                ->withHeaders([
                    'User-Agent' => 'PhuDucBikeSeeder/1.0',
                ])
                ->get($url);
        } catch (\Throwable $exception) {
            $this->command?->warn("Skip image {$fileName}: {$exception->getMessage()}");

            return false;
        }

        if (! $response->successful() || ! str_starts_with((string) $response->header('Content-Type'), 'image/')) {
            $this->command?->warn("Skip image {$fileName}: download failed");

            return false;
        }

        Storage::disk('public')->put($path, $response->body());

        return true;
    }

    private function resolveCommonsImageUrl(string $fileName): ?string
    {
        try {
            $response = Http::timeout(20)
                ->retry(2, 500)
                ->acceptJson()
                ->withHeaders([
                    'User-Agent' => 'PhuDucBikeSeeder/1.0',
                ])
                ->get('https://commons.wikimedia.org/w/api.php', [
                    'action' => 'query',
                    'format' => 'json',
                    'prop' => 'imageinfo',
                    'iiprop' => 'url',
                    'iiurlwidth' => self::WIDTH,
                    'titles' => 'File:' . $fileName,
                ]);
        } catch (\Throwable $exception) {
            $this->command?->warn("Resolve failed {$fileName}: {$exception->getMessage()}");

            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        $pages = $response->json('query.pages', []);
        $page = collect($pages)->first();
        $imageInfo = $page['imageinfo'][0] ?? null;

        return $imageInfo['thumburl'] ?? $imageInfo['url'] ?? null;
    }
}
