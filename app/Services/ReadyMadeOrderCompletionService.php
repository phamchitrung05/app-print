<?php

namespace App\Services;

use App\Models\Orders;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service đồng bộ trạng thái thanh toán cho đơn làm sẵn (ready_made_goods = true).
 *
 * Điều kiện để chuyển order sang trạng thái status_paid = paid
 *  1) TẤT CẢ SKU trong order phải có quantity = 0 trong CustomerStock
 *     (mỗi order_item có 1 dòng customer_stocks via order_item_id)
 *  2) TỔNG các lần payment của MỖI SKU trong order phải đều ở trạng thái hoàn thành
 *     (không còn payment nào của order_item_id đó có payment_status != 'confirmed',
 *      và tất cả các payment của order_item_id đó phải ở trạng thái confirmed)
 *
 * Chỉ khi thỏa đồng thời 2 điều kiện với MỌI order_item thì order mới được chuyển status_paid sang paid
 * (không đụng đến status). Idempotent, an toàn concurrent nhờ lockForUpdate + transaction.
 */
class ReadyMadeOrderCompletionService
{
    /**
     * Thử đồng bộ status_paid = paid cho đơn làm sẵn nếu thỏa 2 điều kiện trên.
     * Không đụng đến status.
     *
     * @return bool true nếu đã chuyển sang paid trong lần gọi này
     */
    public static function tryCompleteOrder(Orders|int $order): bool
    {
        $completed = false;

        DB::transaction(function () use ($order, &$completed): void {
            $lockedOrder = Orders::query()
                ->whereKey($order instanceof Orders ? $order->getKey() : $order)
                ->lockForUpdate()
                ->first();

            if ($lockedOrder === null) {
                return;
            }

            // Chỉ áp dụng cho đơn làm sẵn
            if (! $lockedOrder->ready_made_goods) {
                return;
            }

            // Đã paid rồi thì bỏ qua (idempotent) - không đụng đến status
            if ($lockedOrder->status_paid === 'paid') {
                return;
            }

            $items = $lockedOrder->items()->get();

            // Đơn không có dòng hàng thì không tự hoàn thành
            if ($items->isEmpty()) {
                return;
            }

            foreach ($items as $item) {
                $orderItemId = $item->getKey();

                // Điều kiện 1: CustomerStock quantity phải = 0 cho order_item này
                $stock = DB::table('customer_stocks')
                    ->where('order_item_id', $orderItemId)
                    ->first();

                // Chưa có dòng tồn cho order_item này => chưa đủ điều kiện
                if ($stock === null) {
                    return;
                }

                if ((int) $stock->quantity !== 0) {
                    return;
                }

                // Điều kiện 2: tất cả payment của SKU này phải hoàn thành (confirmed)
                // - phải có ít nhất 1 payment cho SKU này
                // - không còn payment nào ở trạng thái pending/khác confirmed
                $paymentsForSku = DB::table('payments')
                    ->where('order_item_id', $orderItemId);

                $totalCount = (clone $paymentsForSku)->count();

                // Chưa có lần thanh toán nào cho SKU này => chưa hoàn thành
                if ($totalCount === 0) {
                    return;
                }

                $hasUnconfirmed = (clone $paymentsForSku)
                    ->where('payment_status', '!=', 'confirmed')
                    ->exists();

                if ($hasUnconfirmed) {
                    return;
                }
            }

            // Tất cả SKU đã thỏa 2 điều kiện -> chỉ chuyển status_paid sang paid, không đụng status
            $lockedOrder->forceFill(['status_paid' => 'paid'])->saveQuietly();

            // Trừ tồn kho tổng nếu chưa trừ (InventoryService idempotent qua inventory_deducted_at)
            try {
                InventoryService::deductForOrder($lockedOrder);
            } catch (\Throwable $e) {
                Log::warning('ReadyMade auto-complete: deductForOrder failed for order ' . $lockedOrder->code, ['exception' => $e]);
            }

            $completed = true;
        });

        return $completed;
    }

    /**
     * Helper gọi từ order_id.
     */
    public static function tryCompleteByOrderId(int $orderId): bool
    {
        return self::tryCompleteOrder($orderId);
    }

    /**
     * Helper gọi từ order_item_id -> suy ra order_id rồi thử hoàn thành.
     */
    public static function tryCompleteByOrderItemId(int $orderItemId): bool
    {
        $orderId = DB::table('order_items')->where('id', $orderItemId)->value('order_id');

        if ($orderId === null) {
            return false;
        }

        return self::tryCompleteOrder((int) $orderId);
    }
}

