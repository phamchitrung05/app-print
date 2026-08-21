<?php

namespace App\Filament\Resources\Payments\Tables;

use App\Models\Payment;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn (Builder $query): Builder => $query
                    ->with([
                        'order.customer',
                        'order.items',
                        'orderItem.productSku',
                        'confirmedBy',
                    ])
                    ->orderByRaw("CASE WHEN payment_status = 'confirmed' THEN 1 ELSE 0 END")
                    ->orderByDesc('created_at')
            )
            ->columns([
                TextColumn::make('order.code')
                    ->label('Mã đơn hàng')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('order.customer.name')
                    ->label('Khách hàng')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('order.customer.phone')
                    ->label('Số điện thoại')
                    ->copyable()
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('order.ordered_at')
                    ->label('Ngày đặt hàng')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('order.payment_method')
                    ->label('Thanh toán')
                    ->formatStateUsing(
                        fn (?string $state): string => config(
                            "orders.payment_methods.{$state}",
                            $state ?: '—',
                        )
                    )
                    ->badge()
                    ->sortable(),

                TextColumn::make('order.total_price')
                    ->label('Tổng tiền hàng')
                    ->getStateUsing(function (Payment $record): float {
                        $exportQty = (int) ($record->export_volumn ?? 0);

                        // Payment thường (đặt làm / chưa xuất): export_volumn = 0 → hiển thị nguyên giá trị đơn hàng
                        if ($exportQty <= 0) {
                            return (float) ($record->order?->total_price ?? 0);
                        }

                        // Payment từ CustomerStock (xuất kho từng phần):
                        // tổng = total_unit_price tương ứng với hàng được xuất × số lượng xuất
                        // - Ưu tiên lấy total_unit_price từ orderItem liên kết của payment (order_item_id)
                        // - Fallback: nếu payment tạo trước khi có order_item_id, lấy theo productSku của lần xuất
                        $unitPrice = (float) ($record->orderItem?->total_unit_price ?? 0);

                        if ($unitPrice <= 0 && $record->orderItem?->product_sku_id) {
                            // Fallback qua order_items của order theo product_sku_id đã xuất (khi payment chưa có order_item_id)
                            $fallback = $record->order?->items?->firstWhere('product_sku_id', $record->orderItem->product_sku_id);
                            $unitPrice = (float) ($fallback?->total_unit_price ?? 0);
                        }

                        // Fallback cuối cho dữ liệu cũ: lấy total_unit_price dòng đầu (đơn 1 SKU)
                        if ($unitPrice <= 0) {
                            $unitPrice = (float) ($record->order?->items?->first()?->total_unit_price ?? 0);
                        }

                        return $exportQty * $unitPrice;
                    })
                    ->formatStateUsing(
                        fn (float $state): string => number_format($state, 0, ',', '.') . ' ₫'
                    )
                    ->alignEnd()
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        // Sort theo giá trị hiển thị: export_volumn * total_unit_price tương ứng với hàng được xuất
                        return $query
                            ->leftJoin('orders', 'orders.id', '=', 'payments.order_id')
                            ->leftJoin('order_items', 'order_items.id', '=', 'payments.order_item_id')
                            ->orderByRaw(
                                "CASE WHEN COALESCE(payments.export_volumn, 0) > 0 " .
                                "THEN COALESCE(payments.export_volumn, 0) * COALESCE(order_items.total_unit_price, 0) " .
                                "ELSE orders.total_price END " . $direction
                            )
                            ->select('payments.*');
                    })
                    ->description(function (Payment $record): ?string {
                        $exportQty = (int) ($record->export_volumn ?? 0);

                        if ($exportQty <= 0) {
                            return null;
                        }

                        $unit = (float) ($record->orderItem?->total_unit_price ?? 0);
                        $skuLabel = $record->orderItem?->productSku?->sku ? $record->orderItem->productSku->sku : null;

                        if ($unit > 0) {
                            $base = $exportQty . ' × ' . number_format($unit, 0, ',', '.') . ' ₫';
                            return $skuLabel ? $base . ' (' . $skuLabel . ')' : $base;
                        }

                        return 'SL xuất: ' . $exportQty;
                    }),

                TextColumn::make('export_volumn')
                    ->label('SL xuất tính tiền')
                    ->numeric(locale: 'vi')
                    ->sortable()
                    ->alignCenter()
                    ->placeholder('—')
                    ->toggleable(),

                SelectColumn::make('payment_status')
                    ->label('Trạng thái thanh toán')
                    ->options(config('orders.payment_statuses'))
                    ->disabled(fn (Payment $record): bool => $record->payment_status === 'confirmed')
                    ->selectablePlaceholder(false)
                    ->sortable(),

                TextColumn::make('confirmedBy.name')
                    ->label('Người xác nhận')
                    ->placeholder('—')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('confirmed_at')
                    ->label('Thời điểm xác nhận')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('—')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('customer_id')
                    ->label('Khách hàng')
                    ->relationship('order.customer', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('payment_status')
                    ->label('Trạng thái thanh toán')
                    ->options(config('orders.payment_statuses')),
            ])
            ->recordActions([
                Action::make('viewOrder')
                    ->label('Xem')
                    ->icon(Heroicon::OutlinedEye)
                    ->color('info')
                    ->iconButton()
                    ->modalHeading(
                        fn (Payment $record): string => "Chi tiết đơn hàng: {$record->order->code}"
                    )
                    ->modalContent(function (Payment $record): View {
                        $order = $record->order;
                        $order->loadMissing(['customer', 'items.productSku.product']);

                        return view('filament.payments.order-details', compact('order'));
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Đóng')
                    ->modalWidth('7xl'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
