<?php

namespace App\Models;

use Database\Factories\OrderItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
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

    protected static function booted(): void
    {
        // Trừ tồn kho ngay sau khi dòng hàng được tạo.
        // Do Filament tạo Orders trước, order_items sau từng dòng, nên:
        // - Nếu đơn chưa trừ lần nào: trừ theo tổng hiện có (deductForOrder).
        // - Nếu đơn đã trừ rồi (đã qua dòng đầu): chỉ trừ thêm SKU của dòng mới này (incremental) để không bỏ sót.
        static::created(function (OrderItem $item): void {
            try {
                $order = \App\Models\Orders::find($item->order_id);
                if ($order === null) {
                    return;
                }
                if ($order->status === 'cancelled') {
                    return;
                }

                if ($order->inventory_deducted_at === null) {
                    \App\Services\InventoryService::deductForOrder($order);
                    return;
                }

                // Đã trừ trước đó -> trừ incremental cho dòng mới này
                \Illuminate\Support\Facades\DB::transaction(function () use ($item, $order): void {
                    $sku = \Illuminate\Support\Facades\DB::table('product_skus')
                        ->where('id', $item->product_sku_id)
                        ->lockForUpdate()
                        ->first();

                    if ($sku === null) {
                        throw new \RuntimeException("Không tìm thấy SKU #{$item->product_sku_id} để trừ kho dòng mới của đơn {$order->code}.");
                    }

                    if ((int) $sku->stock < (int) $item->quantity) {
                        throw new \RuntimeException("SKU {$sku->sku} không đủ tồn kho để thêm dòng cho đơn {$order->code}.");
                    }

                    if ((int) $item->quantity > 0) {
                        \Illuminate\Support\Facades\DB::table('product_skus')
                            ->where('id', $sku->id)
                            ->update([
                                'stock' => (int) $sku->stock - (int) $item->quantity,
                                'updated_at' => now(),
                            ]);
                    }
                });
            } catch (\Throwable $e) {
                report($e);
            }
        });

        // Nếu số lượng/SKU thay đổi sau khi đã trừ kho, không tự động điều chỉnh ở đây để tránh race
        // (việc chỉnh tồn nên thực hiện qua InventoryService chuyên biệt khi cần).
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

    public function customerStock(): HasOne
    {
        return $this->hasOne(CustomerStock::class, 'order_item_id');
    }
}
