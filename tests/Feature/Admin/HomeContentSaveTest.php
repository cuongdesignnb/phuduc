<?php

namespace Tests\Feature\Admin;

use App\Http\Controllers\Admin\HomeContentController;
use App\Models\HomeSection;
use App\Models\HomeSectionItem;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Services\Admin\Content\AdminHomeContentService;
use App\Services\Admin\Content\AdminSettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionMethod;
use Tests\TestCase;

class HomeContentSaveTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_save_type_specific_config_and_item_order(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::create(['name' => 'Selected', 'slug' => 'selected', 'status' => 'active', 'price' => 1, 'stock' => 1]);

        $response = $this->actingAs($admin)->post(route('admin.home-content.save'), [
            'version' => $this->homeVersion($admin),
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
            'version' => $this->homeVersion($admin),
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

        $this->actingAs($admin)->postJson(route('admin.home-content.save'), ['version' => $this->homeVersion($admin), 'sections' => [$payload]])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'sections.0.type',
                'sections.0.config.limit',
                'sections.0.config.product_ids.0',
            ]);
    }

    public function test_benefit_strip_rejects_description(): void
    {
        $this->assertBenefitFieldIsRejected('description', 'Unexpected description');
    }

    public function test_benefit_strip_rejects_image(): void
    {
        $this->assertBenefitFieldIsRejected('image', 'unexpected.webp');
    }

    public function test_partner_rejects_avatar_text(): void
    {
        $admin = User::factory()->admin()->create();
        $payload = $this->sectionPayload('partners', 'item_collection', 'logo_grid', 10, [], [
            $this->strictItemPayload(
                ['title' => 'Partner', 'image' => 'partner.webp', 'url' => '/partner'],
                ['avatar_text' => 'PX']
            ),
        ]);

        $this->actingAs($admin)->postJson(route('admin.home-content.save'), ['version' => $this->homeVersion($admin), 'sections' => [$payload]])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('sections.0.items.0.metadata');

        $this->assertDatabaseCount('home_sections', 0);
        $this->assertDatabaseCount('home_section_items', 0);
    }

    public function test_testimonial_accepts_avatar_text(): void
    {
        $admin = User::factory()->admin()->create();
        $payload = $this->sectionPayload('testimonials', 'item_collection', 'quote_cards', 10, [], [
            $this->strictItemPayload(
                ['title' => 'Customer', 'subtitle' => 'Director', 'description' => 'Trusted', 'image' => null],
                ['avatar_text' => 'CD']
            ),
        ]);

        $this->actingAs($admin)->post(route('admin.home-content.save'), ['version' => $this->homeVersion($admin), 'sections' => [$payload]])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertSame('CD', HomeSectionItem::query()->firstOrFail()->metadata_json['avatar_text']);
    }

    public function test_industry_solution_accepts_tone(): void
    {
        $admin = User::factory()->admin()->create();
        $payload = $this->sectionPayload('industry_solutions', 'item_collection', 'industry_grid', 10, [], [
            $this->strictItemPayload(
                ['title' => 'Logistics', 'image' => null, 'url' => '/solutions/logistics'],
                ['tone' => 'emerald']
            ),
        ]);

        $this->actingAs($admin)->post(route('admin.home-content.save'), ['version' => $this->homeVersion($admin), 'sections' => [$payload]])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertSame('emerald', HomeSectionItem::query()->firstOrFail()->metadata_json['tone']);
    }

    public function test_unknown_item_key_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();
        $payload = $this->sectionPayload('benefit_strip', 'item_collection', 'icon_strip', 10, [], [
            [...$this->itemPayload('Benefit', 0), 'unexpected' => 'malicious'],
        ]);

        $this->actingAs($admin)->postJson(route('admin.home-content.save'), ['version' => $this->homeVersion($admin), 'sections' => [$payload]])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('sections.0.items.0');

        $this->assertDatabaseCount('home_sections', 0);
        $this->assertDatabaseCount('home_section_items', 0);
    }

    public function test_controller_does_not_persist_disallowed_item_fields(): void
    {
        $section = $this->storedSection('benefit_strip', 'icon_strip', 'Benefits');
        $method = new ReflectionMethod(HomeContentController::class, 'syncItems');
        $method->invoke(app(HomeContentController::class), $section, [[
            ...$this->itemPayload('Warranty', 0),
            'description' => 'Must not be persisted',
            'image' => 'unexpected.webp',
            'url' => 'https://unexpected.example',
            'metadata' => ['avatar_text' => 'XX'],
        ]], ['title', 'icon']);

        $item = HomeSectionItem::query()->firstOrFail();
        $this->assertSame('Warranty', $item->title);
        $this->assertSame('shield', $item->icon);
        $this->assertNull($item->description);
        $this->assertNull($item->image);
        $this->assertNull($item->url);
        $this->assertSame([], $item->metadata_json);
    }

    public function test_legacy_home_settings_cannot_be_saved_through_settings(): void
    {
        $admin = User::factory()->admin()->create();
        Setting::set('home.hero_title', 'Legacy title');

        $this->actingAs($admin)->postJson(route('admin.settings.save'), [
            'version' => $this->settingsVersion($admin),
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

    private function homeVersion(User $admin): string
    {
        return app(AdminHomeContentService::class)->page($admin)['page']['module']['version'];
    }

    private function settingsVersion(User $admin): string
    {
        return app(AdminSettingService::class)->page($admin)['page']['module']['version'];
    }

    private function itemPayload(string $title, int $order): array
    {
        return $this->strictItemPayload(['title' => $title, 'icon' => 'shield'], [], $order);
    }

    private function strictItemPayload(array $fields, array $metadata = [], int $order = 0): array
    {
        return [
            'id' => null,
            ...$fields,
            'metadata' => $metadata,
            'enabled' => true,
            'sort_order' => $order,
        ];
    }

    private function assertBenefitFieldIsRejected(string $field, string $value): void
    {
        $admin = User::factory()->admin()->create();
        $section = $this->storedSection('benefit_strip', 'icon_strip', 'Original section');
        $item = HomeSectionItem::create([
            'home_section_id' => $section->id,
            'section_key' => $section->key,
            'title' => 'Original item',
            'icon' => 'shield',
            'is_active' => true,
            'sort_order' => 0,
        ]);
        $payload = $this->sectionPayload('benefit_strip', 'item_collection', 'icon_strip', 10, [], [[
            ...$this->itemPayload('Changed item', 0),
            'id' => $item->id,
            $field => $value,
        ]], 'Changed section');

        $this->actingAs($admin)->postJson(route('admin.home-content.save'), ['version' => $this->homeVersion($admin), 'sections' => [$payload]])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('sections.0.items.0');

        $this->assertDatabaseHas('home_sections', ['id' => $section->id, 'title' => 'Original section']);
        $this->assertDatabaseHas('home_section_items', ['id' => $item->id, 'title' => 'Original item']);
    }
}
