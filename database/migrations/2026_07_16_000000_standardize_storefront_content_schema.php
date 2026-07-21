<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Frozen snapshot used by this historical migration. Keep this independent
     * from the mutable application registry so future UI changes cannot alter
     * the result of a fresh deployment.
     */
    private const SECTION_DEFINITIONS = [
        'hero' => ['type' => 'hero', 'variant' => 'industrial_marketplace', 'label' => 'Hero'],
        'category_cards' => ['type' => 'item_collection', 'variant' => 'cards', 'label' => 'Danh mục'],
        'benefit_strip' => ['type' => 'item_collection', 'variant' => 'icon_strip', 'label' => 'Cam kết dịch vụ'],
        'featured_products' => ['type' => 'product_collection', 'variant' => 'marketplace_grid', 'label' => 'Sản phẩm nổi bật'],
        'energy_banner' => ['type' => 'content_banner', 'variant' => 'green_energy', 'label' => 'Banner năng lượng'],
        'industry_solutions' => ['type' => 'item_collection', 'variant' => 'industry_grid', 'label' => 'Giải pháp theo ngành'],
        'testimonials' => ['type' => 'item_collection', 'variant' => 'quote_cards', 'label' => 'Khách hàng nói gì'],
        'partners' => ['type' => 'item_collection', 'variant' => 'logo_grid', 'label' => 'Đối tác'],
        'latest_posts' => ['type' => 'post_collection', 'variant' => 'editorial_grid', 'label' => 'Tin tức mới nhất'],
        'consultation_steps' => ['type' => 'item_collection', 'variant' => 'numbered_steps', 'label' => 'Các bước tư vấn'],
    ];

    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false)->index()->after('password');
        });

        Schema::table('home_sections', function (Blueprint $table) {
            $table->string('type')->nullable()->after('key');
            $table->string('variant')->nullable()->after('description');
            $table->index('sort_order');
            $table->index('is_enabled');
        });

        Schema::table('home_section_items', function (Blueprint $table) {
            $table->foreignId('home_section_id')
                ->nullable()
                ->after('id')
                ->constrained('home_sections')
                ->cascadeOnDelete();
            $table->index(['home_section_id', 'is_active', 'sort_order'], 'home_items_active_order_idx');
        });

        $this->normalizeLegacySectionKey();
        $this->backfillSectionSchema();
        $this->copyLegacySettingsWithoutOverwritingContent();
        $this->backfillSectionRelations();
    }

    public function down(): void
    {
        if (DB::table('home_sections')->where('key', 'benefits')->doesntExist()) {
            $benefitStrip = DB::table('home_sections')->where('key', 'benefit_strip')->first();
            if ($benefitStrip) {
                DB::table('home_section_items')->where('home_section_id', $benefitStrip->id)->update(['section_key' => 'benefits']);
                DB::table('home_sections')->where('id', $benefitStrip->id)->update(['key' => 'benefits']);
            }
        }

        Schema::table('home_section_items', function (Blueprint $table) {
            $table->dropForeign(['home_section_id']);
            $table->dropIndex('home_items_active_order_idx');
            $table->dropColumn('home_section_id');
        });

        Schema::table('home_sections', function (Blueprint $table) {
            $table->dropIndex(['sort_order']);
            $table->dropIndex(['is_enabled']);
            $table->dropColumn(['type', 'variant']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['is_admin']);
            $table->dropColumn('is_admin');
        });
    }

    private function normalizeLegacySectionKey(): void
    {
        $legacy = DB::table('home_sections')->where('key', 'benefits')->first();

        if ($legacy && DB::table('home_sections')->where('key', 'benefit_strip')->doesntExist()) {
            DB::table('home_sections')->where('id', $legacy->id)->update(['key' => 'benefit_strip']);
            DB::table('home_section_items')->where('section_key', 'benefits')->update(['section_key' => 'benefit_strip']);
        }
    }

    private function backfillSectionSchema(): void
    {
        foreach (self::SECTION_DEFINITIONS as $key => $definition) {
            DB::table('home_sections')
                ->where('key', $key)
                ->update([
                    'type' => $definition['type'],
                    'variant' => DB::raw("COALESCE(variant, '".str_replace("'", "''", $definition['variant'])."')"),
                ]);
        }
    }

    private function copyLegacySettingsWithoutOverwritingContent(): void
    {
        $settings = DB::table('settings')->where('key', 'like', 'home.%')->pluck('value', 'key');

        if ($settings->isEmpty()) {
            return;
        }

        $sections = [
            'hero' => [
                'title' => $settings->get('home.hero_title'),
                'subtitle' => $settings->get('home.hero_subtitle'),
                'config' => [
                    'image' => $settings->get('home.hero_image'),
                    'primary_cta' => [
                        'label' => $settings->get('home.hero_primary_label'),
                        'url' => $settings->get('home.hero_primary_url'),
                    ],
                    'secondary_cta' => [
                        'label' => $settings->get('home.hero_secondary_label'),
                        'action' => 'phone',
                    ],
                ],
            ],
            'featured_products' => [
                'title' => $settings->get('home.featured_products_title'),
                'config' => [
                    'source' => 'latest',
                    'limit' => (int) ($settings->get('home.featured_products_limit') ?: 4),
                    'product_ids' => [],
                ],
            ],
            'energy_banner' => [
                'title' => $settings->get('home.energy_title'),
                'description' => $settings->get('home.energy_description'),
                'config' => [
                    'eyebrow' => $settings->get('home.energy_eyebrow'),
                    'stats' => [
                        ['label' => $settings->get('home.energy_stat_1_label'), 'value' => $settings->get('home.energy_stat_1_value')],
                        ['label' => $settings->get('home.energy_stat_2_label'), 'value' => $settings->get('home.energy_stat_2_value')],
                    ],
                ],
            ],
            'latest_posts' => [
                'title' => $settings->get('home.latest_posts_title'),
                'config' => [
                    'source' => 'latest',
                    'limit' => (int) ($settings->get('home.latest_posts_limit') ?: 3),
                    'post_ids' => [],
                ],
            ],
        ];

        $nextOrder = ((int) DB::table('home_sections')->max('sort_order')) + 10;

        foreach ($sections as $key => $legacy) {
            $definition = self::SECTION_DEFINITIONS[$key];
            $section = DB::table('home_sections')->where('key', $key)->first();

            if (! $section) {
                DB::table('home_sections')->insert([
                    'key' => $key,
                    'type' => $definition['type'],
                    'title' => $legacy['title'] ?: $definition['label'],
                    'subtitle' => $legacy['subtitle'] ?? null,
                    'description' => $legacy['description'] ?? null,
                    'variant' => $definition['variant'],
                    'is_enabled' => true,
                    'sort_order' => $nextOrder,
                    'settings_json' => json_encode($legacy['config'], JSON_UNESCAPED_UNICODE),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $nextOrder += 10;

                continue;
            }

            $updates = [];
            if (blank($section->title) && filled($legacy['title'] ?? null)) {
                $updates['title'] = $legacy['title'];
            }
            if (blank($section->subtitle) && filled($legacy['subtitle'] ?? null)) {
                $updates['subtitle'] = $legacy['subtitle'];
            }
            if (blank($section->description) && filled($legacy['description'] ?? null)) {
                $updates['description'] = $legacy['description'];
            }
            if (blank($section->settings_json)) {
                $updates['settings_json'] = json_encode($legacy['config'], JSON_UNESCAPED_UNICODE);
            }

            if ($updates !== []) {
                $updates['updated_at'] = now();
                DB::table('home_sections')->where('id', $section->id)->update($updates);
            }
        }
    }

    private function backfillSectionRelations(): void
    {
        DB::table('home_sections')->orderBy('id')->each(function (object $section): void {
            DB::table('home_section_items')
                ->whereNull('home_section_id')
                ->where('section_key', $section->key)
                ->update(['home_section_id' => $section->id]);
        });
    }
};
