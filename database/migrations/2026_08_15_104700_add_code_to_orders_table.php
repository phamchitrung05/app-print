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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('code', 5)->nullable()->unique()->after('uuid');
        });

        DB::table('orders')
            ->select('id')
            ->orderBy('id')
            ->each(function (object $order): void {
                DB::table('orders')
                    ->where('id', $order->id)
                    ->update([
                        'code' => str_pad((string) $order->id, 5, '0', STR_PAD_LEFT),
                    ]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['code']);
            $table->dropColumn('code');
        });
    }
};
