<?php

namespace App\Models;

use Database\Factories\CustomerStockFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerStock extends Model
{
    /** @use HasFactory<CustomerStockFactory> */
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'product_sku_id',
        'order_item_id',
        'quantity',
        'status',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function productSku(): BelongsTo
    {
        return $this->belongsTo(ProductSKU::class, 'product_sku_id');
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id');
    }
}
