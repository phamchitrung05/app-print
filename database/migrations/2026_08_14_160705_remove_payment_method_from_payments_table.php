<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('payments')
            ->whereNotIn('payment_status', ['pending', 'confirmed'])
            ->update([
                'payment_status' => 'pending',
                'confirmed_by' => null,
                'confirmed_at' => null,
            ]);

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('payment_method');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('payment_method')->default('cash')->after('order_id');
        });
    }
};
