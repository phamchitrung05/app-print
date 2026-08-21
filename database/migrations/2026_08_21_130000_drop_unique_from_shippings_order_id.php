<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bỏ ràng buộc unique trên shippings.order_id để mỗi lần xuất kho
     * từ Tồn Kho Sẵn Hàng đều tạo được 1 record shipping riêng
     * (lưu số lượng xuất vào export_volume).
     */
    public function up(): void
    {
        Schema::table('shippings', function (Blueprint $table): void {
            // Xóa unique index được tạo ở migration create_shippings_table (tên mặc định shippings_order_id_unique)
            $table->dropUnique(['order_id']);
            // Giữ index thường để truy vấn theo order_id vẫn nhanh
            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::table('shippings', function (Blueprint $table): void {
            $table->dropIndex(['order_id']);
            $table->unique('order_id');
        });
    }
};
