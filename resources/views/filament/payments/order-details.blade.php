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

<div class="space-y-7">
    {{-- Total card --}}
    <section class="rounded-2xl border border-gray-200 bg-gray-50 px-6 py-5 dark:border-white/10 dark:bg-white/[0.03]">
        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Tổng thanh toán</p>
        <div class="mt-1 flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-3xl font-bold tracking-tight text-gray-950 dark:text-white">
                    {{ number_format($total, 0, ',', '.') }} ₫
                </p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ $order->ordered_at?->format('d/m/Y H:i') ?? '—' }}
                </p>
            </div>

            <div class="rounded-xl bg-primary-100 p-3 text-primary-600 dark:bg-primary-500/20 dark:text-primary-400">
                <x-heroicon-o-banknotes class="h-7 w-7" />
            </div>
        </div>
    </section>

    {{-- Information --}}
    <section class="grid gap-8 md:grid-cols-2">
        <div>
            <h3 class="mb-5 text-base font-bold text-gray-950 dark:text-white">Thông tin đơn hàng</h3>
            <dl class="space-y-4">
                <div>
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Mã đơn hàng</dt>
                    <dd class="mt-1 font-semibold text-primary-600 dark:text-primary-400">{{ $order->code }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Khách hàng</dt>
                    <dd class="mt-1 font-semibold text-gray-950 dark:text-white">{{ $customer?->name ?? '—' }}</dd>
                    <dd class="text-sm text-gray-500 dark:text-gray-400">{{ $customer?->phone ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Địa chỉ</dt>
                    <dd class="mt-1 font-medium text-gray-950 dark:text-white">{{ $customer?->address ?? '—' }}</dd>
                </div>
            </dl>
        </div>

        <div>
            <h3 class="mb-5 text-base font-bold text-gray-950 dark:text-white">Thông tin thanh toán</h3>
            <dl class="space-y-4">
                <div>
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Phương thức thanh toán</dt>
                    <dd class="mt-1 flex items-center gap-2 font-semibold text-gray-950 dark:text-white">
                        <span class="inline-flex rounded-lg bg-blue-50 p-2 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400">
                            <x-heroicon-o-credit-card class="h-4 w-4" />
                        </span>
                        {{ $paymentMethod }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Tổng tiền hàng</dt>
                    <dd class="mt-1 font-semibold text-gray-950 dark:text-white">{{ number_format($subtotal, 0, ',', '.') }} ₫</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Chiết khấu</dt>
                    <dd class="mt-1 font-semibold text-gray-950 dark:text-white">{{ number_format($discount, 0, ',', '.') }} ₫</dd>
                </div>
            </dl>
        </div>
    </section>

    {{-- Note --}}
    @if ($order->note)
        <section>
            <h3 class="mb-3 text-base font-bold text-gray-950 dark:text-white">Ghi chú</h3>
            <div class="rounded-xl border border-gray-200 bg-gray-50 px-5 py-4 text-sm text-gray-600 dark:border-white/10 dark:bg-white/[0.03] dark:text-gray-300">
                <p class="whitespace-pre-line">{{ $order->note }}</p>
            </div>
        </section>
    @endif

    {{-- Products --}}
    <section>
        <div class="mb-3 flex items-center justify-between gap-3">
            <h3 class="text-base font-bold text-gray-950 dark:text-white">Sản phẩm trong đơn hàng</h3>
            <span class="text-sm text-gray-500 dark:text-gray-400">{{ $order->items->count() }} sản phẩm</span>
        </div>

        <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-white/10">
            <div class="overflow-x-auto">
                <table class="w-full divide-y divide-gray-200 text-sm dark:divide-white/10">
                    <thead class="bg-gray-50 dark:bg-white/5">
                        <tr>
                            <th class="px-5 py-3 text-left font-semibold text-gray-700 dark:text-gray-200">Sản phẩm</th>
                            <th class="px-5 py-3 text-right font-semibold text-gray-700 dark:text-gray-200">SL</th>
                            <th class="px-5 py-3 text-right font-semibold text-gray-700 dark:text-gray-200">Đơn giá</th>
                            <th class="px-5 py-3 text-right font-semibold text-gray-700 dark:text-gray-200">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                        @forelse ($order->items as $item)
                            <tr>
                                <td class="px-5 py-3 font-medium text-gray-950 dark:text-white">
                                    {{ $item->product?->name ?? 'Sản phẩm không tồn tại' }}
                                </td>
                                <td class="px-5 py-3 text-right text-gray-600 dark:text-gray-300">
                                    {{ number_format((float) $item->quantity, 0, ',', '.') }}
                                </td>
                                <td class="px-5 py-3 text-right text-gray-600 dark:text-gray-300">
                                    {{ number_format((float) $item->total_unit_price, 0, ',', '.') }} ₫
                                </td>
                                <td class="px-5 py-3 text-right font-semibold text-gray-950 dark:text-white">
                                    {{ number_format((float) $item->quantity * (float) $item->total_unit_price, 0, ',', '.') }} ₫
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-8 text-center text-gray-500 dark:text-gray-400">
                                    Đơn hàng chưa có sản phẩm.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>
