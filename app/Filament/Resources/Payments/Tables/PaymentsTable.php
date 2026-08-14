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
                fn (Builder $query): Builder => $query->with([
                    'order.customer',
                    'confirmedBy',
                ])
            )
            ->columns([
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
                    ->formatStateUsing(
                        fn (string | int | float | null $state): string => number_format(
                            (float) ($state ?? 0),
                            0,
                            ',',
                            '.',
                        ) . ' ₫'
                    )
                    ->alignEnd()
                    ->sortable(),

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
                SelectFilter::make('payment_status')
                    ->label('Trạng thái thanh toán')
                    ->options(config('orders.payment_statuses')),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                Action::make('viewOrder')
                    ->label('Xem')
                    ->icon(Heroicon::OutlinedEye)
                    ->color('info')
                    ->iconButton()
                    ->modalHeading(
                        fn (Payment $record): string => "Chi tiết đơn hàng: {$record->order->uuid}"
                    )
                    ->modalContent(function (Payment $record): View {
                        $order = $record->order;
                        $order->loadMissing(['customer', 'items.product']);

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
