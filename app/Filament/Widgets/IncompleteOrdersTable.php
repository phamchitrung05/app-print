<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Orders\Schemas\OrdersForm;
use App\Models\Orders;
use Filament\Actions\ViewAction;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
class IncompleteOrdersTable extends TableWidget
{
    protected static ?string $heading = 'Đơn hàng chưa hoàn thành';

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Orders::query()
                    ->where('status', '!=', 'completed')
                    ->with('customer')
            )
            ->defaultSort('ordered_at', 'desc')
            ->columns([
                TextColumn::make('code')
                    ->label('Mã đơn hàng')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('customer.name')
                    ->label('Khách hàng')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer.phone')
                    ->label('Số điện thoại')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('ordered_at')
                    ->label('Ngày đặt hàng')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Trạng thái')
                    ->formatStateUsing(
                        fn (?string $state): string => config('orders.statuses')[$state] ?? (string) $state
                    )
                    ->badge(),

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
            ])
            ->recordActions([
                ViewAction::make()
                    ->iconButton()
                    ->modalHeading(fn (Orders $record): string => "Chi tiết đơn hàng: {$record->code}")
                    ->schema(fn (Schema $schema): Schema => OrdersForm::configure($schema))
                    ->modalWidth('7xl'),
            ])
            ->paginated([5, 10, 25]);
    }
}
