<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->string('checkout_intent', 128)->nullable()->unique()->after('order_number');
            $table->string('public_token', 128)->nullable()->unique()->after('checkout_intent');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropUnique(['checkout_intent']);
            $table->dropUnique(['public_token']);
            $table->dropColumn(['checkout_intent', 'public_token']);
        });
    }
};
