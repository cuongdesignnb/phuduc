<?php

namespace Tests\Feature\Seeders;

use App\Models\HomeSection;
use App\Models\HomeSectionItem;
use App\Models\Setting;
use Database\Seeders\ProductionDefaultsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionDefaultsSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_production_defaults_do_not_create_admin_accounts(): void
    {
        $this->seed(ProductionDefaultsSeeder::class);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_production_defaults_are_idempotent_and_do_not_overwrite_admin_content(): void
    {
        $this->seed(ProductionDefaultsSeeder::class);
        Setting::where('key', 'site.name')->update(['value' => 'Admin site name']);
        HomeSection::where('key', 'hero')->update([
            'title' => 'Admin hero',
            'sort_order' => 777,
            'settings_json' => ['primary_cta' => ['label' => 'Admin CTA', 'url' => '/admin-choice']],
        ]);
        $defaultItem = HomeSectionItem::query()->where('section_key', 'benefit_strip')->firstOrFail();
        $defaultItemTitle = $defaultItem->title;
        $defaultItem->update(['title' => 'Admin renamed benefit']);

        $this->seed(ProductionDefaultsSeeder::class);

        $this->assertSame('Admin site name', Setting::get('site.name'));
        $hero = HomeSection::where('key', 'hero')->firstOrFail();
        $this->assertSame('Admin hero', $hero->title);
        $this->assertSame(777, $hero->sort_order);
        $this->assertSame('Admin CTA', $hero->settings_json['primary_cta']['label']);
        $this->assertDatabaseHas('home_section_items', ['id' => $defaultItem->id, 'title' => 'Admin renamed benefit']);
        $this->assertDatabaseMissing('home_section_items', ['section_key' => 'benefit_strip', 'title' => $defaultItemTitle]);
    }
}
