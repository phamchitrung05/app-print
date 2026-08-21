@php
    $c=$order?->customer; $its=$order?->items??collect(); $pay=$order?->payments?->first();
    $ps=$pay?->payment_status??'pending'; $isPaid=$ps==='confirmed';
    $pl=config('orders.payment_statuses')[$ps]??($isPaid?'Đã thanh toán':'Chưa thanh toán');
    $pm=config('orders.payment_methods')[$order->payment_method??'']??($order->payment_method??'—');
    $sub=$its->sum(fn($i)=>(float)($i->quantity??0)*(float)($i->total_unit_price??0));
    $tot=(float)($order->total_price??$sub); $dis=(float)($order->discount??0); $ship=0; $grand=max(0,$tot-$dis+$ship);
    $at=$order?->ordered_at?\Illuminate\Support\Carbon::parse($order->ordered_at):null;
    $cd=($order->code??'')!==''?'#'.$order->code:'—';
@endphp
@if(!$order)
    <div
        class="rounded-xl border border-dashed border-gray-300 p-8 text-center text-sm text-gray-500 dark:border-gray-600">
        Tồn kho này không liên kết đơn hàng (tồn tạo thủ công).
    </div>
@else
    <div class="space-y-4 text-lg leading-5 antialiased">
        <div class="space-y-1.5 text-base">
            <div class="flex flex-wrap items-center gap-2">
                <span class="font-bold tracking-tight text-gray-900 dark:text-white">{{$cd}}</span>
                @if($isPaid)
                    <span
                        class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-1 text-sm font-semibold leading-none text-emerald-700 ring-1 ring-inset ring-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/20">Đã thanh toán</span>
                @else
                    <span
                        class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-1 text-sm font-semibold leading-none text-amber-700 ring-1 ring-inset ring-amber-200 dark:bg-amber-500/10 dark:text-amber-400 dark:ring-amber-500/20">{{$pl}}</span>
                @endif
            </div>
            <div class="flex items-center gap-1.5 text-slate-500 dark:text-gray-400">
                <svg class="h-3.5 w-3.5 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7H3v12a2 2 0 002 2z"/>
                </svg>
                <span>Đặt lúc {{$at?->format('H:i')??'—'}}</span><span
                    class="h-1 w-1 rounded-full bg-gray-400"></span><span>{{$at?->format('d/m/Y')??'—'}}</span>
            </div>
        </div>
        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
            <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800/40">
                <div class="mb-3 flex items-center gap-2">
                    <span
                        class="flex h-7 w-7 items-center justify-center rounded-full bg-blue-50 text-blue-600 ring-1 ring-blue-100 dark:bg-blue-500/10 dark:text-blue-400 dark:ring-blue-500/20"><svg
                            class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path
                                stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg></span>
                    <span class="text-lg font-semibold text-gray-900 dark:text-white">Thông tin khách hàng</span>
                </div>
                <div class="space-y-3">
                    <div class="text-[14px] font-semibold text-gray-900 dark:text-white">{{$c->name??'—'}}</div>
                    <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                        <svg class="h-3.5 w-3.5 shrink-0 text-gray-400" fill="none" stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-2C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        <span>{{$c->phone??'—'}}</span></div>
                    <div class="flex items-start gap-2 text-sm leading-5 text-gray-600 dark:text-gray-300">
                        <svg class="mt-0.5 h-3.5 w-3.5 shrink-0 text-gray-400" fill="none" stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span>{{$c->address??'—'}}</span></div>
                </div>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800/40">
                <div class="mb-3 flex items-center gap-2"><span
                        class="flex h-7 w-7 items-center justify-center rounded-full bg-violet-50 text-violet-600 ring-1 ring-violet-100 dark:bg-violet-500/10 dark:text-violet-400 dark:ring-violet-500/20"><svg
                            class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path
                                stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 10h18M7 15h1m4 0h1m-7 4h12a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg></span><span
                        class="text-lg font-semibold text-gray-900 dark:text-white">Thông tin thanh toán</span></div>
                <div class="space-y-3">
                    <div class="flex items-center justify-between gap-4"><span
                            class="text-sm text-gray-500 dark:text-gray-400">Phương thức thanh toán</span><span
                            class="text-sm font-medium text-gray-900 dark:text-white">{{$pm}}</span></div>
                    <div class="flex items-center justify-between gap-4"><span
                            class="text-sm text-gray-500 dark:text-gray-400">Trạng thái thanh toán</span>@if($isPaid)
                            <span
                                class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-1 text-sm font-semibold leading-none text-emerald-700 ring-1 ring-inset ring-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/20">Đã thanh toán</span>
                        @else
                            <span
                                class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-1 text-sm font-semibold leading-none text-amber-700 ring-1 ring-inset ring-amber-200 dark:bg-amber-500/10 dark:text-amber-400 dark:ring-amber-500/20">{{$pl}}</span>
                        @endif</div>
                    <div class="flex items-center justify-between gap-4"><span
                            class="text-sm text-gray-500 dark:text-gray-400">Tạm tính</span><span
                            class="text-sm font-medium text-gray-900 dark:text-white">{{number_format($sub,0,',','.')}} ₫</span>
                    </div>

                    <div class="flex items-center justify-between gap-4"><span
                            class="text-sm text-gray-500 dark:text-gray-400">Giảm giá</span><span
                            class="text-sm font-medium text-red-500">-{{number_format($dis,0,',','.')}} ₫</span></div>
                    <div
                        class="mt-3 flex items-center justify-between gap-4 border-t border-gray-200 pt-3 dark:border-gray-700">
                        <span class="text-[13px] font-semibold text-gray-900 dark:text-white">Tổng cộng</span><span
                            class="text-[15px] font-bold text-[#007aff] dark:text-blue-400">{{number_format($grand,0,',','.')}} ₫</span>
                    </div>
                </div>
            </div>
        </div>

        <div
            class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800/40">
            <div class="flex items-center gap-2 border-b border-gray-200 px-4 py-3 dark:border-gray-700"><span
                    class="flex h-7 w-7 items-center justify-center rounded-full bg-blue-50 text-blue-600 ring-1 ring-blue-100 dark:bg-blue-500/10 dark:text-blue-400"><svg
                        class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg></span><span
                    class="text-lg font-semibold text-gray-900 dark:text-white">Sản phẩm đặt hàng</span></div>
            <div class="p-3">
                <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
                    <table class="min-w-full text-sm">
                        <thead>
                        <tr class="bg-gray-50 text-sm] font-semibold uppercase tracking-wide text-gray-500 dark:bg-gray-900/40 dark:text-gray-400">
                            <th class="px-4 py-2.5 text-left">Sản phẩm</th>
                            <th class=" px-4 py-2.5 text-right">Đơn giá</th>
                            <th class="px-4 py-2.5 text-center">Số lượng</th>
                            <th class="px-4 py-2.5 text-right">Thành tiền</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-transparent">
                        @foreach($its as $it)
                            @php $sku=$it->productSku; $pn=$sku?->product?->name??'—'; $img=$sku?->product?->image_url??null; $line=(float)($it->quantity??0)*(float)($it->total_unit_price??0); $vr=$sku->variant_name??$sku->sku??null; @endphp
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="min-w-0 flex-1">
                                            <div
                                                class="truncate text-sm font-medium leading-5 text-gray-900 dark:text-white">{{$pn}}</div>@if($vr)
                                                <div class="text-xs leading-4 text-gray-500 dark:text-gray-400">
                                                    SKU: {{$vr}}</div>
                                            @endif</div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-right text-sm font-medium tabular-nums text-gray-900 dark:text-white">{{number_format((float)($it->total_unit_price??0),0,',','.')}}
                                    ₫
                                </td>
                                <td class="px-4 py-3 text-center text-sm tabular-nums text-gray-900 dark:text-white">{{$it->quantity}}</td>
                                <td class="px-4 py-3 text-right text-sm font-semibold tabular-nums text-gray-900 dark:text-white">{{number_format($line,0,',','.')}}
                                    ₫
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @if($order->note)
            <div
                class="rounded-xl border border-amber-200 bg-amber-50/60 px-4 py-3 text-sm leading-5 dark:border-amber-900/30 dark:bg-amber-900/10">
                <span class="font-semibold text-gray-900 dark:text-white">Ghi chú: </span><span
                    class="text-gray-700 dark:text-gray-300">{{$order->note}}</span></div>
        @endif
    </div>
@endif


