<?php

namespace App\Services;

use App\Models\Orders;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Service xử lý trừ tồn kho SKU khi đơn hàng đủ điều kiện.
 * - Đảm bảo không trừ trùng (idempotent) qua cột inventory_deducted_at.
 * - An toàn khi nhiều request chạy song song nhờ row lock (SELECT ... FOR UPDATE).
 */
class InventoryService
{
    /**
     * Trừ tồn kho cho một đơn hàng.
     *
     * Điều kiện được phép trừ: đã thanh toán (payment confirmed) HOẶC đã giao hàng (shipping delivered)
     * HOẶC đơn đã chuyển sang trạng thái completed. Nếu chưa thỏa thì thoát sớm không trừ.
     * Gom số lượng theo SKU (SUM + GROUP BY) để xử lý đúng khi đơn có nhiều dòng cùng SKU.
     * Khóa các dòng product_skus theo thứ tự id để tránh deadlock, kiểm tra đủ tồn rồi mới trừ.
     *
     * @param Orders|int $order Model hoặc ID đơn hàng cần trừ kho
     */
    public static function deductForOrder(Orders|int $order): void
    {
        // Bọc toàn bộ trong transaction để đảm bảo tính toàn vẹn: hoặc trừ hết, hoặc rollback hết
        DB::transaction(function () use ($order): void {
            // Khóa dòng đơn hàng (FOR UPDATE) để tránh 2 tiến trình cùng trừ 1 đơn gây trừ trùng
            $lockedOrder = Orders::query()
                ->whereKey($order instanceof Orders ? $order->getKey() : $order)
                ->lockForUpdate()
                ->firstOrFail();

            // Nếu đã trừ kho trước đó rồi thì bỏ qua (idempotent - gọi lại vẫn an toàn)
            if ($lockedOrder->inventory_deducted_at !== null) {
                return;
            }

            // Kiểm tra đã thanh toán thành công chưa (payment_status = confirmed)
            $hasSuccessfulPayment = $lockedOrder->payments()
                ->where('payment_status', 'confirmed')
                ->exists();

            // Kiểm tra đã giao hàng chưa (shipping_status = delivered)
            $hasDeliveredShipping = $lockedOrder->shipping()
                ->where('shipping_status', 'delivered')
                ->exists();

            // Kiểm tra đơn đã chuyển sang hoàn thành chưa (status = completed) - yêu cầu mới từ khách
            $isCompleted = $lockedOrder->status === 'completed';

            // Nếu chưa thỏa bất kỳ điều kiện nào thì chưa được phép trừ kho -> thoát sớm
            if (! $hasSuccessfulPayment && ! $hasDeliveredShipping && ! $isCompleted) {
                return;
            }

            // Gom số lượng cần trừ theo từng SKU: SUM(quantity) GROUP BY product_sku_id
            // Sắp xếp theo product_sku_id để thứ tự khóa luôn cố định, tránh deadlock khi lock nhiều dòng
            $quantitiesBySku = $lockedOrder->items()
                ->selectRaw('product_sku_id, SUM(quantity) as quantity')
                ->groupBy('product_sku_id')
                ->orderBy('product_sku_id')
                ->get()
                ->mapWithKeys(fn ($item): array => [
                    (int) $item->product_sku_id => (int) $item->quantity,
                ]);

            // Lấy danh sách ID các SKU cần xử lý
            $skuIds = $quantitiesBySku->keys();

            // Khóa các dòng product_skus tương ứng (FOR UPDATE) theo thứ tự id tăng dần
            // Nếu đơn không có item thì trả về collection rỗng để không query thừa
            $skus = $skuIds->isEmpty()
                ? collect()
                : DB::table('product_skus')
                    ->whereIn('id', $skuIds)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

            // Duyệt từng SKU để kiểm tra tồn và trừ kho
            foreach ($quantitiesBySku as $skuId => $quantity) {
                $sku = $skus->get($skuId);

                // Nếu không tìm thấy SKU trong DB thì dữ liệu không nhất quán -> báo lỗi để rollback transaction
                if ($sku === null) {
                    throw new RuntimeException(
                        "Không tìm thấy SKU #{$skuId} của đơn hàng {$lockedOrder->code}."
                    );
                }

                // Kiểm tra tồn kho có đủ không; nếu thiếu hoặc quantity âm thì không cho trừ và rollback
                if ($quantity < 0 || (int) $sku->stock < $quantity) {
                    throw new RuntimeException(
                        "SKU {$sku->sku} không đủ tồn kho để xử lý đơn hàng {$lockedOrder->code}."
                    );
                }

                // Chỉ UPDATE khi quantity > 0 để tránh ghi DB thừa
                if ($quantity > 0) {
                    DB::table('product_skus')
                        ->where('id', $sku->id)
                        ->update([
                            'stock' => (int) $sku->stock - $quantity, // Trừ trực tiếp trên giá trị đã khóa
                            'updated_at' => now(),
                        ]);
                }
            }

            // Đánh dấu đơn đã trừ kho để lần sau không trừ lại
            // Dùng saveQuietly để không kích hoạt lại event updated (tránh vòng lặp vô hạn)
            $lockedOrder->forceFill([
                'inventory_deducted_at' => now(),
            ])->saveQuietly();
        });
    }
}
