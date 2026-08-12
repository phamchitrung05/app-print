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
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('customer_id')
                ->nullable()
                ->constrained()
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->uuid('uuid')->nullable()->unique();
            $table->dateTime('ordered_at')->nullable();
            $table->string('status', 50)->default('new');
            $table->string('payment_method', 50)->default('cash');
            $table->text('note')->nullable();

            $table->index(['customer_id', 'ordered_at']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['customer_id', 'ordered_at']);
            $table->dropIndex(['status']);
            $table->dropConstrainedForeignId('customer_id');
            $table->dropColumn([
                'uuid',
                'ordered_at',
                'status',
                'payment_method',
                'note',
            ]);
        });
    }
};
