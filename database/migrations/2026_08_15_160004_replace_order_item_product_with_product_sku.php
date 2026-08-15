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
        Schema::table('order_items', function (Blueprint $table): void {
            $table->dropUnique('order_items_order_id_product_id_unique');
            $table->dropForeign(['product_id']);
            $table->dropIndex(['product_id']);
        });

        Schema::table('order_items', function (Blueprint $table): void {
            $table->renameColumn('sku_id', 'product_sku_id');
            $table->dropColumn('product_id');
        });

        Schema::table('order_items', function (Blueprint $table): void {
            $table->unique(['order_id', 'product_sku_id']);
            $table->index('product_sku_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table): void {
            $table->dropUnique('order_items_order_id_product_sku_id_unique');
            $table->dropIndex(['product_sku_id']);
            $table->renameColumn('product_sku_id', 'sku_id');
            $table->foreignId('product_id')
                ->nullable()
                ->after('order_id')
                ->constrained('products')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });

        Schema::table('order_items', function (Blueprint $table): void {
            $table->unique(['order_id', 'product_id']);
            $table->index('product_id');
        });
    }
};
