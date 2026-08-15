<?php

namespace App\Filament\Resources\Shippings\Tables;

use App\Models\Shipping;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ShippingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn (Builder $query): Builder => $query
                    ->with(['order.customer'])
                    ->orderByRaw("CASE WHEN shipping_status = 'delivered' THEN 1 ELSE 0 END")
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

                TextColumn::make('order.customer.address')
                    ->label('Địa chỉ')
                    ->searchable()
                    ->copyable()
                    ->wrap()
                    ->toggleable(),

                TextColumn::make('order.ordered_at')
                    ->label('Ngày đặt hàng')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                SelectColumn::make('shipping_status')
                    ->label('Trạng thái giao hàng')
                    ->options([
                        'pending' => 'Chưa giao',
                        'delivered' => 'Đã giao',
                    ])
                    ->disabled(fn (Shipping $record): bool => $record->shipping_status === 'delivered')
                    ->selectablePlaceholder(false)
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('shipping_status')
                    ->label('Trạng thái giao hàng')
                    ->options([
                        'pending' => 'Chưa giao',
                        'delivered' => 'Đã giao',
                    ]),
            ])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
