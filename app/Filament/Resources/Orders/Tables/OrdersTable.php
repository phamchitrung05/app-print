<?php

namespace App\Filament\Resources\Orders\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('ordered_at', 'desc')
            ->columns([
                TextColumn::make('uuid')
                    ->label('Mã đơn hàng')
                    ->searchable()
                    ->copyable()
                    ->limit(16)
                    ->tooltip(fn (?string $state): ?string => $state),

                TextColumn::make('customer.name')
                    ->label('Khách hàng')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer.phone')
                    ->label('Số điện thoại')
                    ->copyable()
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('ordered_at')
                    ->label('Ngày đặt hàng')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Trạng thái')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'new' => 'Mới tạo',
                        'confirmed' => 'Đã xác nhận',
                        'processing' => 'Đang xử lý',
                        'completed' => 'Hoàn thành',
                        'cancelled' => 'Đã hủy',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'new' => 'gray',
                        'confirmed' => 'info',
                        'processing' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('payment_method')
                    ->label('Thanh toán')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'cash' => 'Tiền mặt',
                        'bank_transfer' => 'Chuyển khoản',
                        'card' => 'Thẻ',
                        'e_wallet' => 'Ví điện tử',
                        default => $state,
                    })
                    ->badge()
                    ->color('gray'),

                TextColumn::make('total_price')
                    ->label('Tổng tiền')
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

                TextColumn::make('note')
                    ->label('Ghi chú')
                    ->limit(40)
                    ->tooltip(fn (?string $state): ?string => $state)
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Trạng thái')
                    ->options([
                        'new' => 'Mới tạo',
                        'confirmed' => 'Đã xác nhận',
                        'processing' => 'Đang xử lý',
                        'completed' => 'Hoàn thành',
                        'cancelled' => 'Đã hủy',
                    ]),

                SelectFilter::make('payment_method')
                    ->label('Phương thức thanh toán')
                    ->options([
                        'cash' => 'Tiền mặt',
                        'bank_transfer' => 'Chuyển khoản',
                        'card' => 'Thẻ',
                        'e_wallet' => 'Ví điện tử',
                    ]),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Chỉnh sửa'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
