@php
    $formatMoney = static fn (float $amount): string => number_format($amount, 0, ',', '.') . ' ₫';
@endphp

<div class="space-y-6">
    <div class="flex items-center gap-4">
        <div class="flex h-16 w-16 items-center justify-center rounded-xl bg-primary-50 text-primary-600 dark:bg-primary-500/10">
            <x-heroicon-o-cube class="h-9 w-9" />
        </div>

        <div>
            <h2 class="text-xl font-bold text-gray-950 dark:text-white">
                {{ $sku->product?->name ?? 'Sản phẩm' }}
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                SKU: <span class="font-medium text-gray-700 dark:text-gray-300">{{ $sku->sku }}</span>
                <span class="mx-1">·</span>
                Giá bán: <span class="font-medium text-gray-700 dark:text-gray-300">{{ $formatMoney((float) $sku->price) }}</span>
                <span class="mx-1">·</span>
                Tồn kho: <span class="font-medium text-gray-700 dark:text-gray-300">{{ number_format((int) $sku->stock, 0, ',', '.') }}</span>
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center gap-3">
                <div class="rounded-lg bg-blue-50 p-2 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400">
                    <x-heroicon-o-shopping-bag class="h-6 w-6" />
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Đơn vị bán trong {{ $monthLabel }}</p>
                    <p class="mt-1 text-2xl font-bold text-gray-950 dark:text-white">
                        {{ number_format($totalQuantity, 0, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center gap-3">
                <div class="rounded-lg bg-green-50 p-2 text-green-600 dark:bg-green-500/10 dark:text-green-400">
                    <x-heroicon-o-banknotes class="h-6 w-6" />
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Doanh thu trong {{ $monthLabel }}</p>
                    <p class="mt-1 text-2xl font-bold text-gray-950 dark:text-white">
                        {{ $formatMoney($totalRevenue) }}
                    </p>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center gap-3">
                <div class="rounded-lg bg-orange-50 p-2 text-orange-600 dark:bg-orange-500/10 dark:text-orange-400">
                    <x-heroicon-o-receipt-percent class="h-6 w-6" />
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Số đơn hàng trong {{ $monthLabel }}</p>
                    <p class="mt-1 text-2xl font-bold text-gray-950 dark:text-white">
                        {{ number_format($totalOrders, 0, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
            <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-700">
                <h3 class="font-semibold text-gray-950 dark:text-white">Top đơn hàng</h3>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Theo doanh thu trong {{ $monthLabel }}</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                        <tr>
                            <th class="px-4 py-3">Mã đơn</th>
                            <th class="px-4 py-3">Khách hàng</th>
                            <th class="px-4 py-3 text-right">SL</th>
                            <th class="px-4 py-3 text-right">Doanh thu</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($topOrders as $order)
                            <tr>
                                <td class="whitespace-nowrap px-4 py-3 font-medium text-primary-600">
                                    {{ $order['code'] }}
                                </td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                                    {{ $order['customer'] }}
                                </td>
                                <td class="px-4 py-3 text-right text-gray-700 dark:text-gray-300">
                                    {{ number_format($order['quantity'], 0, ',', '.') }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right font-medium text-gray-950 dark:text-white">
                                    {{ $formatMoney($order['revenue']) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                                    Chưa có đơn hàng trong tháng này.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
            <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-700">
                <h3 class="font-semibold text-gray-950 dark:text-white">Thống kê theo tháng</h3>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Số liệu của 6 tháng gần nhất</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                        <tr>
                            <th class="px-4 py-3">Tháng</th>
                            <th class="px-4 py-3 text-right">Đơn vị bán</th>
                            <th class="px-4 py-3 text-right">Doanh thu</th>
                            <th class="px-4 py-3 text-right">Đơn hàng</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($monthlyStatistics as $statistic)
                            <tr>
                                <td class="px-4 py-3 font-medium text-gray-950 dark:text-white">
                                    {{ $statistic['month'] }}
                                </td>
                                <td class="px-4 py-3 text-right text-gray-700 dark:text-gray-300">
                                    {{ number_format($statistic['quantity'], 0, ',', '.') }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right text-gray-700 dark:text-gray-300">
                                    {{ $formatMoney($statistic['revenue']) }}
                                </td>
                                <td class="px-4 py-3 text-right text-gray-700 dark:text-gray-300">
                                    {{ number_format($statistic['orders'], 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
