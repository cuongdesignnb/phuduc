<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            $table->string('meta_title')->nullable()->after('status');
            $table->text('meta_description')->nullable()->after('meta_title');
            $table->string('meta_keywords')->nullable()->after('meta_description');
        });

        Schema::table('media_libraries', function (Blueprint $table): void {
            $table->string('caption')->nullable()->after('alt_text');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            $table->dropColumn(['meta_title', 'meta_description', 'meta_keywords']);
        });
        Schema::table('media_libraries', function (Blueprint $table): void {
            $table->dropColumn('caption');
        });
    }
};
