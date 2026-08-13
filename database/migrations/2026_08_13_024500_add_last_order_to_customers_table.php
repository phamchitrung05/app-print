<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dateTime('last_order')->nullable()->after('option');
        });

        DB::table('orders')
            ->select('customer_id', DB::raw('MAX(ordered_at) as last_order'))
            ->whereNotNull('customer_id')
            ->groupBy('customer_id')
            ->orderBy('customer_id')
            ->each(function (object $order): void {
                DB::table('customers')
                    ->where('id', $order->customer_id)
                    ->update(['last_order' => $order->last_order]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('last_order');
        });
    }
};
