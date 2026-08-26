<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Only normalize the historical seeded value; never overwrite a custom brand.
        DB::table('settings')
            ->where('key', 'site.name')
            ->where('value', 'Phú Đức - Xe Điện Công Nghiệp')
            ->update(['value' => 'Phú Đức']);
    }

    public function down(): void
    {
        DB::table('settings')
            ->where('key', 'site.name')
            ->where('value', 'Phú Đức')
            ->update(['value' => 'Phú Đức - Xe Điện Công Nghiệp']);
    }
};
