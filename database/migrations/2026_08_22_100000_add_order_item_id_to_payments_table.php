<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Liên kết payment với dòng order_item đã xuất để tính tiền chính xác theo SKU.
     * - order_item_id nullable: payment thường (đặt làm, export_volumn=0) không có dòng cụ thể.
     * - Khi xuất từ CustomerStock: lưu order_item_id của CustomerStock.order_item_id để PaymentsTable
     *   lấy đúng order_items.total_unit_price tương ứng × export_volumn.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            $table->foreignId('order_item_id')
                ->nullable()
                ->after('order_id')
                ->constrained('order_items')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->index('order_item_id');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('order_item_id');
        });
    }
};
