<?php

namespace App\Services;

use App\Models\Orders;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class InventoryService
{
    public static function deductForOrder(Orders|int $order): void
    {
        DB::transaction(function () use ($order): void {
            $lockedOrder = Orders::query()
                ->whereKey($order instanceof Orders ? $order->getKey() : $order)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedOrder->inventory_deducted_at !== null) {
                return;
            }

            $hasSuccessfulPayment = $lockedOrder->payments()
                ->where('payment_status', 'confirmed')
                ->exists();

            $hasDeliveredShipping = $lockedOrder->shipping()
                ->where('shipping_status', 'delivered')
                ->exists();

            if (! $hasSuccessfulPayment && ! $hasDeliveredShipping) {
                return;
            }

            $quantitiesBySku = $lockedOrder->items()
                ->selectRaw('product_sku_id, SUM(quantity) as quantity')
                ->groupBy('product_sku_id')
                ->orderBy('product_sku_id')
                ->get()
                ->mapWithKeys(fn ($item): array => [
                    (int) $item->product_sku_id => (int) $item->quantity,
                ]);

            $skuIds = $quantitiesBySku->keys();

            $skus = $skuIds->isEmpty()
                ? collect()
                : DB::table('product_skus')
                    ->whereIn('id', $skuIds)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

            foreach ($quantitiesBySku as $skuId => $quantity) {
                $sku = $skus->get($skuId);

                if ($sku === null) {
                    throw new RuntimeException(
                        "Không tìm thấy SKU #{$skuId} của đơn hàng {$lockedOrder->code}."
                    );
                }

                if ($quantity < 0 || (int) $sku->stock < $quantity) {
                    throw new RuntimeException(
                        "SKU {$sku->sku} không đủ tồn kho để xử lý đơn hàng {$lockedOrder->code}."
                    );
                }

                if ($quantity > 0) {
                    DB::table('product_skus')
                        ->where('id', $sku->id)
                        ->update([
                            'stock' => (int) $sku->stock - $quantity,
                            'updated_at' => now(),
                        ]);
                }
            }

            $lockedOrder->forceFill([
                'inventory_deducted_at' => now(),
            ])->saveQuietly();
        });
    }
}
