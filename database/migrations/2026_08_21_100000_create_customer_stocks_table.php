<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_stocks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('customer_id')
                ->constrained('customers')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('product_sku_id')
                ->constrained('product_skus')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('order_item_id')
                ->nullable()
                ->unique()
                ->constrained('order_items')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->unsignedInteger('quantity')->default(0);
            $table->string('status', 50)->default('pending');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'product_sku_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_stocks');
    }
};
