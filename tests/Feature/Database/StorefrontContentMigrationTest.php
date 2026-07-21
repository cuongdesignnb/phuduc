<?php

namespace Tests\Feature\Database;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StorefrontContentMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_migration_does_not_promote_a_hardcoded_email(): void
    {
        $source = $this->migrationSource();

        $this->assertStringNotContainsString('admin@phuducev.vn', $source);

        DB::table('users')->insert([
            'name' => 'Production user',
            'email' => 'admin@phuducev.vn',
            'password' => Hash::make('not-a-deployment-secret'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'admin@phuducev.vn',
            'is_admin' => false,
        ]);
    }

    public function test_migration_uses_a_frozen_canonical_section_snapshot(): void
    {
        $source = $this->migrationSource();
        $keys = [
            'hero',
            'category_cards',
            'benefit_strip',
            'featured_products',
            'energy_banner',
            'industry_solutions',
            'testimonials',
            'partners',
            'latest_posts',
            'consultation_steps',
        ];

        $this->assertStringNotContainsString('HomeSectionRegistry', $source);
        $this->assertStringContainsString('private const SECTION_DEFINITIONS', $source);

        foreach ($keys as $key) {
            $this->assertStringContainsString("'{$key}' =>", $source);
        }
    }

    private function migrationSource(): string
    {
        $source = file_get_contents(database_path('migrations/2026_07_16_000000_standardize_storefront_content_schema.php'));

        $this->assertNotFalse($source);

        return $source;
    }
}
