<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('warranties', function (Blueprint $table): void {
            $table->foreignId('order_item_id')->nullable()->constrained('order_items')->nullOnDelete();
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('void_reason', 500)->nullable();
            $table->index('customer_phone');
            $table->index('status');
            $table->index('activation_date');
            $table->index('expiration_date');
        });
    }

    public function down(): void
    {
        Schema::table('warranties', function (Blueprint $table): void {
            $table->dropForeign(['order_item_id']);
            $table->dropIndex(['customer_phone']);
            $table->dropIndex(['status']);
            $table->dropIndex(['activation_date']);
            $table->dropIndex(['expiration_date']);
            $table->dropColumn(['order_item_id', 'customer_name', 'customer_phone', 'void_reason']);
        });
    }
};
