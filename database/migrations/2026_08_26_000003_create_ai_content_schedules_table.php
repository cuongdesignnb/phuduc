<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_content_schedules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('post_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('post_categories')->nullOnDelete();
            $table->string('topic');
            $table->json('keywords')->nullable();
            $table->string('type', 50)->default('article');
            $table->string('tone', 30)->default('professional');
            $table->string('length', 30)->default('medium');
            $table->boolean('with_images')->default(false);
            $table->unsignedTinyInteger('image_count')->default(1);
            $table->boolean('auto_publish')->default(false);
            $table->string('status', 30)->default('pending');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamp('scheduled_at');
            $table->timestamp('locked_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'scheduled_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_content_schedules');
    }
};
