<?php

namespace App\Models;

use App\Services\ReadyMadeOrderCompletionService;
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

    protected static function booted(): void
    {
        // Khi tồn kho thay đổi (thường là sau khi xuất kho), thử auto-complete đơn làm sẵn
        static::updated(function (CustomerStock $stock): void {
            if (! $stock->wasChanged('quantity') && ! $stock->wasChanged('status')) {
                return;
            }

            if ($stock->order_item_id === null) {
                return;
            }

            try {
                ReadyMadeOrderCompletionService::tryCompleteByOrderItemId((int) $stock->order_item_id);
            } catch (\Throwable $e) {
                report($e);
            }
        });
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
