<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('order_items', 'sku_id')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->foreignId('sku_id')
                    ->nullable()
                    ->after('product_id')
                    ->constrained('product_skus')
                    ->nullOnDelete()
                    ->cascadeOnUpdate();

                $table->index('sku_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('order_items', 'sku_id')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->dropForeign(['sku_id']);
                $table->dropIndex(['sku_id']);
                $table->dropColumn('sku_id');
            });
        }
    }
};
