<?php

namespace Tests\Feature\Admin;

use App\Models\MediaLibrary;
use App\Models\Product;
use App\Services\Admin\Catalog\ProductReferenceService;
use App\Services\Admin\Media\MediaReferenceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminBatchQueryBehaviorTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_reference_queries_are_bounded_by_batch_size(): void
    {
        $products = collect(range(1, 30))->map(fn (int $number) => Product::create(['name' => 'Product '.$number, 'slug' => 'product-'.$number, 'status' => 'active']));
        $service = app(ProductReferenceService::class);
        $one = $this->countQueries(fn () => $service->forProducts([$products->first()->id]));
        $many = $this->countQueries(fn () => $service->forProducts($products->pluck('id')->all()));
        fwrite(STDOUT, "PR3B_PRODUCT_REFERENCE_Q1={$one}\nPR3B_PRODUCT_REFERENCE_Q30={$many}\n");
        $this->assertLessThanOrEqual($one + 1, $many);
    }

    public function test_media_reference_queries_are_bounded_by_batch_size(): void
    {
        $media = collect(range(1, 30))->map(fn (int $number) => MediaLibrary::create(['file_name' => 'image-'.$number.'.webp', 'file_path' => 'media/image-'.$number.'.webp', 'mime_type' => 'image/webp', 'size' => 10]));
        $service = app(MediaReferenceService::class);
        $one = $this->countQueries(fn () => $service->forPaths([$media->first()->file_path]));
        $many = $this->countQueries(fn () => $service->forPaths($media->pluck('file_path')->all()));
        fwrite(STDOUT, "PR3B_MEDIA_REFERENCE_Q1={$one}\nPR3B_MEDIA_REFERENCE_Q30={$many}\n");
        $this->assertLessThanOrEqual($one + 1, $many);
    }

    private function countQueries(callable $callback): int
    {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $callback();

        return count(DB::getQueryLog());
    }
}
