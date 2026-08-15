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
        if (! Schema::hasColumn('product_skus', 'name')) {
            Schema::table('product_skus', function (Blueprint $table) {
                $table->string('name')->after('sku');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('product_skus', 'name')) {
            Schema::table('product_skus', function (Blueprint $table) {
                $table->dropColumn('name');
            });
        }
    }
};
