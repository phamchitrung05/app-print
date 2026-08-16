<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_skus', function (Blueprint $table): void {
            $table->dropUnique(['sku']);
        });
    }

    public function down(): void
    {
        Schema::table('product_skus', function (Blueprint $table): void {
            $table->unique('sku');
        });
    }
};
