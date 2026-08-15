<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')
                ->nullable()
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('product_sku_id')
                ->nullable()
                ->constrained('product_skus')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('total_unit_price', 15, 2)->default(0);
            $table->unique(['order_id', 'product_sku_id']);
            $table->index('product_sku_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
