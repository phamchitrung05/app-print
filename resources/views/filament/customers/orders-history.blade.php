<div class="space-y-3">
    <div class="flex items-center justify-between">
        <h3 class="text-base font-semibold text-gray-950 dark:text-white">
            Lịch sử đơn hàng
        </h3>

        <span class="text-sm text-gray-500 dark:text-gray-400">
            {{ $record->orders()->count() }} đơn hàng
        </span>
    </div>

    <div class="max-h-80 overflow-y-auto rounded-xl border border-gray-200 dark:border-white/10">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-50 text-xs uppercase text-gray-600 dark:bg-white/5 dark:text-gray-400">
                <tr>
                    <th scope="col" class="px-4 py-3 font-medium">
                        Mã đơn hàng
                    </th>
                    <th scope="col" class="px-4 py-3 font-medium">
                        Ngày đặt hàng
                    </th>
                    <th scope="col" class="px-4 py-3 text-right font-medium">
                        Tổng tiền
                    </th>
                    <th scope="col" class="px-4 py-3 text-center font-medium">
                        Thao tác
                    </th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                @forelse ($record->orders()->with('items.product')->orderByDesc('ordered_at')->get() as $order)
                    <tr x-data="{ orderDetailsOpen: false }" class="bg-white dark:bg-gray-900">
                        <td class="px-4 py-3 font-medium text-gray-950 dark:text-white">
                            {{ $order->code }}
                        </td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                            {{ $order->ordered_at?->format('d/m/Y H:i') ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-right font-medium text-gray-950 dark:text-white">
                            {{ number_format((float) $order->total_price, 0, ',', '.') }} ₫
                        </td>
                        <td class="px-4 py-3 text-center">
                            <button
                                type="button"
                                x-on:click="orderDetailsOpen = true"
                                title="Xem sản phẩm trong đơn hàng"
                                aria-label="Xem sản phẩm trong đơn hàng {{ $order->code }}"
                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-primary-600 transition hover:bg-primary-50 hover:text-primary-700 focus:outline-none dark:text-primary-400 dark:hover:bg-white/5"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                    aria-hidden="true"
                                    class="h-5 w-5"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12 18 18.75 12 18.75 2.25 12 2.25 12Z"
                                    />
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"
                                    />
                                </svg>
                            </button>

                            <template x-teleport="body">
                                <div
                                    x-cloak
                                    x-show="orderDetailsOpen"
                                    x-on:keydown.escape.window="orderDetailsOpen = false"
                                    class="fixed inset-0 z-[100] overflow-y-auto"
                                    role="dialog"
                                    aria-modal="true"
                                    aria-label="Chi tiết sản phẩm đơn hàng {{ $order->code }}"
                                >
                                    <div
                                        x-show="orderDetailsOpen"
                                        x-transition.opacity
                                        x-on:click="orderDetailsOpen = false"
                                        class="fixed inset-0 bg-gray-950/50"
                                    ></div>

                                    <div class="relative flex min-h-full items-center justify-center p-4">
                                        <div
                                            x-show="orderDetailsOpen"
                                            x-transition
                                            x-on:click.stop
                                            class="w-full max-w-4xl overflow-hidden rounded-xl bg-white shadow-2xl dark:bg-gray-900"
                                        >
                                            <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-white/10">
                                                <div>
                                                    <h3 class="text-lg font-semibold text-gray-950 dark:text-white">
                                                        Sản phẩm trong đơn hàng
                                                    </h3>
                                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                                        {{ $order->code }}
                                                    </p>
                                                </div>

                                                <button
                                                    type="button"
                                                    x-on:click="orderDetailsOpen = false"
                                                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-gray-500 transition hover:bg-gray-100 hover:text-gray-950 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:hover:bg-white/10 dark:hover:text-white"
                                                    aria-label="Đóng modal"
                                                >
                                                    <svg
                                                        xmlns="http://www.w3.org/2000/svg"
                                                        viewBox="0 0 24 24"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        stroke-width="1.5"
                                                        aria-hidden="true"
                                                        class="h-5 w-5"
                                                    >
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m6 6 12 12M6 18 18 6" />
                                                    </svg>
                                                </button>
                                            </div>

                                            <div class="max-h-[60vh] overflow-y-auto">
                                                <table class="w-full text-left text-sm">
                                                    <thead class="sticky top-0 bg-gray-50 text-xs uppercase text-gray-600 dark:bg-white/5 dark:text-gray-400">
                                                        <tr>
                                                            <th scope="col" class="px-6 py-3 font-medium">Sản phẩm</th>
                                                            <th scope="col" class="px-6 py-3 font-medium">Đơn vị</th>
                                                            <th scope="col" class="px-6 py-3 text-right font-medium">Số lượng</th>
                                                            <th scope="col" class="px-6 py-3 text-right font-medium">Đơn giá</th>
                                                            <th scope="col" class="px-6 py-3 text-right font-medium">Thành tiền</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                                                        @forelse ($order->items as $item)
                                                            <tr>
                                                                <td class="px-6 py-4 font-medium text-gray-950 dark:text-white">
                                                                    {{ $item->product?->name ?? 'Sản phẩm không còn tồn tại' }}
                                                                </td>
                                                                <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                                                                    {{ $item->product?->unit ?? '—' }}
                                                                </td>
                                                                <td class="px-6 py-4 text-right text-gray-600 dark:text-gray-400">
                                                                    {{ number_format($item->quantity) }}
                                                                </td>
                                                                <td class="px-6 py-4 text-right text-gray-600 dark:text-gray-400">
                                                                    {{ number_format((float) $item->total_unit_price, 0, ',', '.') }} ₫
                                                                </td>
                                                                <td class="px-6 py-4 text-right font-medium text-gray-950 dark:text-white">
                                                                    {{ number_format((float) $item->total_unit_price * $item->quantity, 0, ',', '.') }} ₫
                                                                </td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                                                    Đơn hàng chưa có sản phẩm nào.
                                                                </td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>

                                                <div class="flex items-center justify-end gap-6 border-t border-gray-200 bg-gray-50 px-6 py-4 text-sm dark:border-white/10 dark:bg-white/5">
                                                    <span class="font-medium text-gray-600 dark:text-gray-400">
                                                        Chiết khấu
                                                    </span>
                                                    <span class="font-semibold text-gray-950 dark:text-white">
                                                        {{ number_format((float) ($order->discount ?? 0), 0, ',', '.') }} ₫
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="flex justify-end border-t border-gray-200 px-6 py-4 dark:border-white/10">
                                                <button
                                                    type="button"
                                                    x-on:click="orderDetailsOpen = false"
                                                    class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-white/10 dark:text-gray-200 dark:hover:bg-white/20"
                                                >
                                                    Đóng
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </td>
                    </tr>
                @empty
                    <tr class="bg-white dark:bg-gray-900">
                        <td colspan="4" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                            Khách hàng chưa có đơn hàng nào.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
