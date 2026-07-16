<?php

namespace Tests\Feature\Admin;

use App\Models\HomeSection;
use App\Models\HomeSectionItem;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeContentSaveTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_save_type_specific_config_and_item_order(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::create(['name' => 'Selected', 'slug' => 'selected', 'status' => 'active', 'price' => 1, 'stock' => 1]);

        $response = $this->actingAs($admin)->post(route('admin.home-content.save'), [
            'sections' => [
                $this->sectionPayload('featured_products', 'product_collection', 'marketplace_grid', 20, [
                    'source' => 'manual', 'limit' => 4, 'product_ids' => [$product->id],
                ]),
                $this->sectionPayload('benefit_strip', 'item_collection', 'icon_strip', 10, [], [
                    $this->itemPayload('Second', 1),
                    $this->itemPayload('First', 0),
                ]),
            ],
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect();
        $this->assertDatabaseHas('home_sections', ['key' => 'benefit_strip', 'sort_order' => 10]);
        $this->assertDatabaseHas('home_sections', ['key' => 'featured_products', 'sort_order' => 20]);
        $this->assertSame([$product->id], HomeSection::where('key', 'featured_products')->first()->settings_json['product_ids']);
        $this->assertSame(['First', 'Second'], HomeSection::where('key', 'benefit_strip')->first()->items()->pluck('title')->all());
    }

    public function test_cross_section_item_update_is_rejected_and_transaction_rolls_back(): void
    {
        $admin = User::factory()->admin()->create();
        $a = $this->storedSection('category_cards', 'cards', 'Original A');
        $b = $this->storedSection('benefit_strip', 'icon_strip', 'Original B');
        $item = HomeSectionItem::create([
            'home_section_id' => $a->id,
            'section_key' => $a->key,
            'title' => 'Owned by A',
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $response = $this->actingAs($admin)->postJson(route('admin.home-content.save'), [
            'sections' => [
                $this->sectionPayload('benefit_strip', 'item_collection', 'icon_strip', 10, [], [
                    [...$this->itemPayload('Hijacked', 0), 'id' => $item->id],
                ], 'Changed B'),
            ],
        ]);

        $response->assertUnprocessable();
        $this->assertDatabaseHas('home_sections', ['id' => $b->id, 'title' => 'Original B']);
        $this->assertDatabaseHas('home_section_items', ['id' => $item->id, 'title' => 'Owned by A', 'home_section_id' => $a->id]);
    }

    public function test_invalid_registry_type_product_id_and_limit_are_rejected(): void
    {
        $admin = User::factory()->admin()->create();
        $payload = $this->sectionPayload('featured_products', 'wrong_type', 'marketplace_grid', 10, [
            'source' => 'manual', 'limit' => 99, 'product_ids' => [999999],
        ]);

        $this->actingAs($admin)->postJson(route('admin.home-content.save'), ['sections' => [$payload]])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'sections.0.type',
                'sections.0.config.limit',
                'sections.0.config.product_ids.0',
            ]);
    }

    public function test_legacy_home_settings_cannot_be_saved_through_settings(): void
    {
        $admin = User::factory()->admin()->create();
        Setting::set('home.hero_title', 'Legacy title');

        $this->actingAs($admin)->postJson(route('admin.settings.save'), [
            'settings' => [[
                'key' => 'home.hero_title',
                'value' => 'Bypass title',
                'type' => 'text',
            ]],
        ])->assertUnprocessable()->assertJsonValidationErrors('settings');

        $this->assertSame('Legacy title', Setting::get('home.hero_title'));
    }

    private function storedSection(string $key, string $variant, string $title): HomeSection
    {
        return HomeSection::create([
            'key' => $key, 'type' => 'item_collection', 'title' => $title, 'variant' => $variant,
            'is_enabled' => true, 'sort_order' => 10, 'settings_json' => [],
        ]);
    }

    private function sectionPayload(string $key, string $type, string $variant, int $order, array $config, array $items = [], ?string $title = null): array
    {
        return [
            'key' => $key,
            'type' => $type,
            'enabled' => true,
            'sort_order' => $order,
            'variant' => $variant,
            'heading' => ['eyebrow' => null, 'title' => $title ?? 'Section title', 'subtitle' => null, 'description' => null],
            'config' => $config,
            'items' => $items,
        ];
    }

    private function itemPayload(string $title, int $order): array
    {
        return [
            'id' => null, 'title' => $title, 'subtitle' => null, 'description' => null,
            'image' => null, 'icon' => 'shield', 'url' => null, 'metadata' => [],
            'enabled' => true, 'sort_order' => $order,
        ];
    }
}
