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
        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('order_id')
                ->nullable()
                ->constrained('orders')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->nullable()
                ->constrained('products')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('total_unit_price', 15, 2)->default(0);

            $table->unique(['order_id', 'product_id']);
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropUnique(['order_id', 'product_id']);
            $table->dropIndex(['product_id']);
            $table->dropConstrainedForeignId('order_id');
            $table->dropConstrainedForeignId('product_id');
            $table->dropColumn([
                'quantity',
                'total_unit_price',
            ]);
        });
    }
};
