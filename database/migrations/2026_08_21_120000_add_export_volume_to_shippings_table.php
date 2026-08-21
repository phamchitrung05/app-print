<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Thêm cột export_volume vào bảng shippings.
     * - Lưu số lượng hàng của mỗi lần giao/xuất kho (mỗi lần bấm Xuất kho từ tồn khách hàng).
     * - Mặc định 0 để các bản ghi cũ và đơn thường (không qua tồn) không bị lỗi.
     */
    public function up(): void
    {
        Schema::table('shippings', function (Blueprint $table): void {
            // Đặt sau shipping_status để dễ đọc schema; integer không âm, mặc định 0
            $table->unsignedInteger('export_volume')->default(0)->after('shipping_status');
        });
    }

    public function down(): void
    {
        Schema::table('shippings', function (Blueprint $table): void {
            $table->dropColumn('export_volume');
        });
    }
};
