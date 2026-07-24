<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! DB::getSchemaBuilder()->hasTable('settings')) {
            return;
        }

        $legacy = DB::table('settings')->where('key', 'seo.og_image')->first();
        $canonical = DB::table('settings')->where('key', 'site.og_image')->first();
        if ($legacy && (! $canonical || blank($canonical->value))) {
            DB::table('settings')->updateOrInsert(
                ['key' => 'site.og_image'],
                ['value' => $legacy->value, 'type' => $legacy->type ?? 'image', 'created_at' => now(), 'updated_at' => now()],
            );
        }
    }

    public function down(): void
    {
        // The compatibility transfer is intentionally not reversed.
    }
};
