<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('customer_id')
                ->nullable()
                ->constrained()
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->uuid('uuid')->nullable()->unique();
            $table->string('code', 5)->nullable()->unique();
            $table->dateTime('ordered_at')->nullable();
            $table->string('status', 50)->default('new');
            $table->decimal('total_price', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->text('note')->nullable();
            $table->index(['customer_id', 'ordered_at']);
            $table->index('status');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
