<?php

namespace Tests\Feature\Storefront;

use App\Models\HomeSection;
use App\Models\HomeSectionItem;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class HomepageContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_returns_canonical_ordered_contract_and_excludes_disabled_content(): void
    {
        $a = $this->section('partners', 'item_collection', 30);
        $b = $this->section('category_cards', 'item_collection', 10, false);
        $c = $this->section('benefit_strip', 'item_collection', 20);

        HomeSectionItem::create([
            'home_section_id' => $c->id,
            'section_key' => $c->key,
            'title' => 'Active item',
            'is_active' => true,
            'sort_order' => 20,
        ]);
        HomeSectionItem::create([
            'home_section_id' => $c->id,
            'section_key' => $c->key,
            'title' => 'Inactive item',
            'is_active' => false,
            'sort_order' => 10,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Guest/Home')
                ->has('site')
                ->has('navigation.header')
                ->has('navigation.footer')
                ->where('page.type', 'home')
                ->has('page.seo')
                ->has('page.sections', 2)
                ->where('page.sections.0.key', 'benefit_strip')
                ->where('page.sections.1.key', 'partners')
                ->where('page.sections.0.items.0.title', 'Active item')
                ->has('page.sections.0', fn (Assert $section) => $section
                    ->hasAll(['key', 'type', 'enabled', 'sort_order', 'variant', 'heading', 'config', 'items'])
                    ->etc())
            );
    }

    public function test_manual_products_preserve_ids_and_real_presentation_data(): void
    {
        $first = $this->product('First', 'first', 'active', now()->subDays(3), [
            ['key' => 'Tải trọng', 'value' => '500 kg'],
            ['key' => 'Pin', 'value' => 'Lithium 80V'],
        ]);
        $inactive = $this->product('Inactive', 'inactive', 'inactive', now());
        $third = $this->product('Third', 'third', 'active', now()->subDay());
        ProductImage::create(['product_id' => $first->id, 'image_path' => 'products/later.webp', 'sort_order' => 20]);
        ProductImage::create(['product_id' => $first->id, 'image_path' => 'products/card.webp', 'sort_order' => 10]);

        Review::create(['product_id' => $first->id, 'customer_name' => 'A', 'content' => 'Good', 'rating' => 4, 'status' => 'approved']);
        Review::create(['product_id' => $first->id, 'customer_name' => 'B', 'content' => 'Pending', 'rating' => 1, 'status' => 'pending']);
        Review::create(['product_id' => $first->id, 'customer_name' => 'C', 'content' => 'Rejected', 'rating' => 2, 'status' => 'rejected']);

        $this->section('featured_products', 'product_collection', 10, true, [
            'source' => 'manual',
            'limit' => 4,
            'product_ids' => [$third->id, $inactive->id, 999999, $first->id],
        ]);

        $this->get('/')->assertInertia(fn (Assert $page) => $page
            ->where('page.sections.0.items.0.id', $third->id)
            ->where('page.sections.0.items.1.id', $first->id)
            ->has('page.sections.0.items', 2)
            ->where('page.sections.0.items.1.specifications.0.value', '500 kg')
            ->where('page.sections.0.items.1.card_specifications.0.value', '500 kg')
            ->where('page.sections.0.items.1.review_count', 1)
            ->where('page.sections.0.items.1.average_rating', 4)
            ->where('page.sections.0.items.1.image_url', url('/storage/products/card.webp'))
        );
    }

    public function test_latest_products_are_active_newest_first_and_honor_limit(): void
    {
        $old = $this->product('Old', 'old', 'active', now()->subDays(2));
        $new = $this->product('New', 'new', 'active', now());
        $this->product('Inactive', 'inactive-latest', 'inactive', now()->addDay());

        $this->section('featured_products', 'product_collection', 10, true, [
            'source' => 'latest',
            'limit' => 2,
            'product_ids' => [],
        ]);

        $this->get('/')->assertInertia(fn (Assert $page) => $page
            ->where('page.sections.0.items.0.id', $new->id)
            ->where('page.sections.0.items.1.id', $old->id)
            ->has('page.sections.0.items', 2)
        );
    }

    public function test_manual_product_source_stays_empty_without_auto_fill(): void
    {
        $inactive = $this->product('Inactive only', 'inactive-only', 'inactive', now());
        $this->product('Latest active', 'latest-active', 'active', now()->addMinute());

        $this->section('featured_products', 'product_collection', 10, true, [
            'source' => 'manual',
            'limit' => 4,
            'product_ids' => [$inactive->id, 999999],
        ]);

        $this->get('/')->assertInertia(fn (Assert $page) => $page
            ->has('page.sections.0.items', 0)
        );
    }

    public function test_homepage_batches_site_settings_into_one_query(): void
    {
        $queries = [];
        DB::listen(function ($query) use (&$queries): void {
            $queries[] = $query->sql;
        });

        $this->get('/')->assertOk();

        $settingsQueries = collect($queries)
            ->filter(function (string $query): bool {
                $query = strtolower($query);

                return str_contains($query, 'from "settings"')
                    || str_contains($query, 'from `settings`');
            });

        $this->assertCount(1, $settingsQueries, $settingsQueries->implode(PHP_EOL));
    }

    private function section(string $key, string $type, int $order, bool $enabled = true, array $config = []): HomeSection
    {
        return HomeSection::create([
            'key' => $key,
            'type' => $type,
            'title' => ucfirst(str_replace('_', ' ', $key)),
            'variant' => match ($key) {
                'partners' => 'logo_grid',
                'category_cards' => 'cards',
                'benefit_strip' => 'icon_strip',
                'featured_products' => 'marketplace_grid',
                default => 'default',
            },
            'is_enabled' => $enabled,
            'sort_order' => $order,
            'settings_json' => $config,
        ]);
    }

    private function product(string $name, string $slug, string $status, $createdAt, array $specifications = []): Product
    {
        return Product::create([
            'name' => $name,
            'slug' => $slug,
            'status' => $status,
            'price' => 1000000,
            'stock' => 1,
            'specifications' => $specifications,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
    }
}
