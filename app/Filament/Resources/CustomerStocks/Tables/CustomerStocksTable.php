<?php

namespace App\Filament\Resources\CustomerStocks\Tables;

use App\Models\Payment;
use App\Models\Shipping;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class CustomerStocksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer.name')
                    ->label('Khách hàng')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('productSku.sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('quantity')
                    ->label('Số lượng')
                    ->numeric(locale: 'vi')
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state <= 0 => 'danger',
                        default => 'info',
                    })
                    ->sortable(),

                // Cột tính tổng tiền lượng hàng còn lại = số lượng tồn × đơn giá
                // - Ưu tiên lấy đơn giá tại thời điểm đặt (orderItem.total_unit_price) để đúng giá lịch sử
                // - Nếu không có orderItem (tồn tạo thủ công) thì fallback sang giá hiện tại của SKU (productSku.price)
                // - Định dạng tiền Việt, có thể sort theo giá trị tính toán qua JOIN
                TextColumn::make('remaining_total')
                    ->label('Tổng tiền còn lại')
                    ->getStateUsing(function ($record): float {
                        // Số lượng còn lại
                        $qty = (int) ($record->quantity ?? 0);
                        // Đơn giá: ưu tiên giá lúc đặt, fallback giá SKU hiện tại
                        $unitPrice = (float) ($record->orderItem?->total_unit_price ?? $record->productSku?->price ?? 0);

                        return $qty * $unitPrice;
                    })
                    ->formatStateUsing(fn (float|int $state): string => number_format((float) $state, 0, ',', '.') . ' ₫')
                    ->alignEnd()
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        // Sort theo biểu thức SQL: quantity * COALESCE(order_items.total_unit_price, product_skus.price, 0)
                        // Dùng leftJoin để vẫn sort được khi order_item_id = null (tồn thủ công)
                        return $query
                            ->leftJoin('order_items', 'order_items.id', '=', 'customer_stocks.order_item_id')
                            ->leftJoin('product_skus', 'product_skus.id', '=', 'customer_stocks.product_sku_id')
                            ->orderByRaw('customer_stocks.quantity * COALESCE(order_items.total_unit_price, product_skus.price, 0) ' . $direction)
                            // Trả về select customer_stocks.* để tránh ambiguous khi đã join
                            ->select('customer_stocks.*');
                    })
                    ->toggleable(),

                TextColumn::make('status')
                    ->label('Trạng thái')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'pending' => 'Chờ xử lý',
                        'in_stock' => 'Còn hàng',
                        'out_of_stock' => 'Hết hàng',
                        'reserved' => 'Đã đặt trước',
                        default => $state ?? '—',
                    })
                    // Yêu cầu mới: trong mục Tồn Kho Sẵn Hàng tất cả ô status đều màu xanh,
                    // chỉ khi hết hàng (out_of_stock) thì chuyển sang đỏ và dòng đó sẽ được đưa xuống cuối bảng
                    // -> ở đây đổi màu badge: out_of_stock = danger (đỏ), còn lại = infor (xanh)
                    ->color(fn (?string $state): string => match ($state) {
                        'out_of_stock' => 'danger',
                        default => 'info',
                    }),

                TextColumn::make('note')
                    ->label('Ghi chú')
                    ->limit(30)
                    ->toggleable(),

                TextColumn::make('updated_at')
                    ->label('Cập nhật')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('customer_id')
                    ->label('Khách hàng')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload()
                    ->native(false),
            ])
            ->recordActions([
                // Nút "Sửa" chuyển thành modal xuất kho:
                // - Hiện ô input "Số Lượng Hàng Xuất" với max = tồn hiện tại (quantity)
                // - Khi lưu sẽ tạo 1 record shipping và trừ tồn kho tương ứng
                Action::make('viewOrder')
                    ->label('Xem đơn hàng')
                    ->icon(Heroicon::OutlinedEye)
                    ->color('info')
                    ->iconButton()
                    ->modalHeading(fn ($record): string => $record->orderItem?->order?->code ? "Đơn hàng {$record->orderItem->order->code}" : 'Đơn hàng')
                    ->modalWidth('7xl')
                    ->modalContent(fn ($record) => view('filament.customer-stocks.view-order', [
                        'order' => $record->orderItem?->order()->with(['customer', 'items.productSku.product'])->first(),
                    ]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Đóng')
                    ->visible(fn ($record): bool => $record->order_item_id !== null),

                Action::make('export')
                    ->label('Xuất kho')
                    ->icon(Heroicon::OutlinedPencilSquare)
                    ->iconButton()
                    ->modalHeading('Xuất hàng tồn kho')
                    ->modalDescription(fn ($record): string => "Tồn hiện tại: {$record->quantity} — Nhập số lượng muốn xuất, hệ thống sẽ tạo vận chuyển và trừ tồn.")
                    ->modalSubmitActionLabel('Xuất kho')
                    ->modalWidth('md')
                    // Vô hiệu hóa khi đã hết hàng để tránh xuất âm
                    ->disabled(fn ($record): bool => (int) $record->quantity <= 0)
                    ->form([
                        // Ô nhập số lượng xuất: bắt buộc, min 1, max = tồn hiện tại (động theo record)
                        TextInput::make('export_quantity')
                            ->label('Số Lượng Hàng Xuất')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(fn ($record): int => (int) $record->quantity)
                            ->helperText(fn ($record): string => 'Tối đa: ' . $record->quantity)
                            ->placeholder('Nhập số lượng cần xuất...'),
                    ])
                    ->action(function ($record, array $data): void {
                        // Lấy số lượng cần xuất từ form modal (đã validate min/max)
                        $exportQty = (int) ($data['export_quantity'] ?? 0);

                        // Dùng transaction + lock dòng để tránh race condition khi 2 người cùng xuất
                        DB::transaction(function () use ($record, $exportQty): void {
                            // Khóa dòng customer_stocks hiện tại để đọc quantity chính xác nhất
                            $locked = \App\Models\CustomerStock::query()
                                ->whereKey($record->getKey())
                                ->lockForUpdate()
                                ->firstOrFail();

                            // Kiểm tra lại tồn sau khi lock (đảm bảo không vượt tồn do concurrent)
                            if ($exportQty <= 0 || $exportQty > (int) $locked->quantity) {
                                throw new \RuntimeException("Số lượng xuất không hợp lệ. Tồn hiện tại: {$locked->quantity}, yêu cầu xuất: {$exportQty}.");
                            }

                            // Xác định order_id để tạo shipping (ưu tiên từ orderItem liên kết của tồn làm sẵn)
                            $orderId = $locked->orderItem?->order_id;

                            // Nếu tồn không liên kết đơn hàng thì không thể tạo shipping (cột order_id unique + required)
                            if ($orderId === null) {
                                throw new \RuntimeException('Tồn kho này không liên kết đơn hàng nên không thể tạo vận chuyển. Vui lòng liên kết order_item trước.');
                            }

                            // Mỗi lần xuất đều tạo 1 record shipping + 1 record payment riêng
                            // - shipping lưu export_volume, payment lưu export_volumn (so luong moi lan xuat)
                            // - Khong dung firstOrCreate vi moi lan xuat la 1 dong doc lap
                            Shipping::create([
                                'order_id' => $orderId,
                                'shipping_status' => 'pending',
                                'export_volume' => $exportQty,
                            ]);

                            Payment::create([
                                'order_id' => $orderId,
                                'order_item_id' => $locked->order_item_id,
                                'payment_status' => 'pending',
                                'export_volumn' => $exportQty,
                            ]);

                            // Tính tồn mới sau khi xuất
                            $newQty = (int) $locked->quantity - $exportQty;

                            // Cập nhật tồn kho: trừ quantity và đổi status nếu về 0
                            $locked->forceFill([
                                'quantity' => $newQty,
                                'status' => $newQty <= 0 ? 'out_of_stock' : $locked->status,
                            ])->save();
                        });

                        // Thông báo thành công
                        Notification::make()
                            ->title("Đã xuất {$exportQty} và tạo vận chuyển/thanh toán thành công")
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(
                fn (Builder $query): Builder => $query
                    ->with(['orderItem', 'productSku'])
                    ->orderByRaw("CASE WHEN customer_stocks.status = 'out_of_stock' THEN 1 ELSE 0 END")
                    ->orderByDesc('customer_stocks.updated_at')
            );
    }
}
