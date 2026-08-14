@php
    $customer = $order->customer;
    $subtotal = $order->items->sum(
        fn ($item): float => (float) $item->quantity * (float) $item->total_unit_price
    );
    $discount = max(0, (float) ($order->discount ?? 0));
    $total = max(0, $subtotal - $discount);
    $status = config("orders.statuses.{$order->status}", $order->status ?: '—');
    $paymentMethod = config(
        "orders.payment_methods.{$order->payment_method}",
        $order->payment_method ?: '—',
    );
@endphp

<div class="space-y-6">
    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Mã đơn hàng</p>
            <p class="font-semibold text-gray-950 dark:text-white">{{ $order->uuid }}</p>
        </div>
        <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Ngày đặt hàng</p>
            <p class="font-medium text-gray-950 dark:text-white">{{ $order->ordered_at?->format('d/m/Y H:i') ?? '—' }}</p>
        </div>
        <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Trạng thái đơn hàng</p>
            <p class="font-medium text-gray-950 dark:text-white">{{ $status }}</p>
        </div>
        <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Khách hàng</p>
            <p class="font-medium text-gray-950 dark:text-white">{{ $customer?->name ?? '—' }}</p>
        </div>
        <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Số điện thoại</p>
            <p class="font-medium text-gray-950 dark:text-white">{{ $customer?->phone ?? '—' }}</p>
        </div>
        <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Phương thức thanh toán</p>
            <p class="font-medium text-gray-950 dark:text-white">{{ $paymentMethod }}</p>
        </div>
        <div class="md:col-span-2 xl:col-span-3">
            <p class="text-sm text-gray-500 dark:text-gray-400">Địa chỉ</p>
            <p class="font-medium text-gray-950 dark:text-white">{{ $customer?->address ?? '—' }}</p>
        </div>
        <div class="md:col-span-2 xl:col-span-3">
            <p class="text-sm text-gray-500 dark:text-gray-400">Ghi chú</p>
            <p class="whitespace-pre-line font-medium text-gray-950 dark:text-white">{{ $order->note ?: '—' }}</p>
        </div>
    </section>

    <section>
        <h3 class="mb-3 text-base font-semibold text-gray-950 dark:text-white">Sản phẩm trong đơn hàng</h3>
        <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-white/10">
            <table class="w-full divide-y divide-gray-200 text-sm dark:divide-white/10">
                <thead class="bg-gray-50 dark:bg-white/5">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">Sản phẩm</th>
                        <th class="px-4 py-3 text-right font-semibold">Số lượng</th>
                        <th class="px-4 py-3 text-right font-semibold">Đơn giá</th>
                        <th class="px-4 py-3 text-right font-semibold">Thành tiền</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                    @forelse ($order->items as $item)
                        <tr>
                            <td class="px-4 py-3">{{ $item->product?->name ?? 'Sản phẩm không tồn tại' }}</td>
                            <td class="px-4 py-3 text-right">{{ number_format((float) $item->quantity, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right">{{ number_format((float) $item->total_unit_price, 0, ',', '.') }} ₫</td>
                            <td class="px-4 py-3 text-right font-medium">{{ number_format((float) $item->quantity * (float) $item->total_unit_price, 0, ',', '.') }} ₫</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-gray-500">Đơn hàng chưa có sản phẩm.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="ml-auto grid max-w-md gap-3">
        <div class="flex justify-between"><span>Tổng tiền hàng</span><strong>{{ number_format($subtotal, 0, ',', '.') }} ₫</strong></div>
        <div class="flex justify-between"><span>Chiết khấu</span><strong>{{ number_format($discount, 0, ',', '.') }} ₫</strong></div>
        <div class="flex justify-between border-t border-gray-200 pt-3 text-lg dark:border-white/10"><strong>Tổng thanh toán</strong><strong class="text-primary-600">{{ number_format($total, 0, ',', '.') }} ₫</strong></div>
    </section>
</div>
