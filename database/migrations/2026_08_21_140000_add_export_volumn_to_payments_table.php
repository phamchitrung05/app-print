<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Them cot export_volumn vao bang payments.
     * - Luu so luong moi lan thanh toan (moi record payment tuong ung 1 lan thanh toan).
     * - Mac dinh 0 de cac ban ghi cu khong bi loi.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            // Dat sau payment_status de de doc schema; integer khong am, mac dinh 0
            $table->unsignedInteger('export_volumn')->default(0)->after('payment_status');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            $table->dropColumn('export_volumn');
        });
    }
};
