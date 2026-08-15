<?php

namespace App\Models;

use Database\Factories\OrderItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class OrderItem extends Model
{
    /** @use HasFactory<OrderItemFactory> */
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_sku_id',
        'quantity',
        'total_unit_price',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'total_unit_price' => 'decimal:2',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Orders::class);
    }

    public function productSku(): BelongsTo
    {
        return $this->belongsTo(ProductSKU::class, 'product_sku_id');
    }

    public function product(): HasOneThrough
    {
        return $this->hasOneThrough(
            Product::class,
            ProductSKU::class,
            'id',
            'id',
            'product_sku_id',
            'product_id',
        );
    }
}
