<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            $table->foreignId('author_id')->nullable()->after('post_category_id')->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable()->after('status')->index();
        });

        // Historical published rows have no explicit publication event. Preserve their
        // existing chronology once, rather than manufacturing a date at crawl time.
        DB::table('posts')
            ->where('status', 'published')
            ->whereNull('published_at')
            ->update(['published_at' => DB::raw('created_at')]);
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            $table->dropIndex(['published_at']);
            $table->dropForeign(['author_id']);
            $table->dropColumn(['author_id', 'published_at']);
        });
    }
};
