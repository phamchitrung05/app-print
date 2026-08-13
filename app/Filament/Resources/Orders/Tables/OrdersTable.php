<?php

namespace App\Filament\Resources\Orders\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Filament\Resources\Orders\Schemas\OrdersForm;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Support\Icons\Heroicon;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('ordered_at', 'desc')
            ->columns([
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

                SelectColumn::make('status')
                    ->label('Trạng thái')
                    ->options(config('orders.statuses'))
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
                    ->badge(),

                TextColumn::make('total_price')
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
                    ->options(config('orders.statuses')),

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
                ViewAction::make()
                    ->iconButton()
                    ->tooltip('Xem chi tiết')
                    ->modalHeading(fn ($record): string => "Chi tiết đơn hàng: {$record->uuid}")
                    ->schema(fn (Schema $schema): Schema => OrdersForm::configure($schema))
                    ->modalWidth('7xl'),

                EditAction::make()
                    ->iconButton()
                    ->tooltip('Chỉnh sửa'),

                Action::make('reorder')
                    ->label('Đặt lại đơn này')
                    ->icon(Heroicon::OutlinedArrowPath)
                    ->iconButton()
                    ->tooltip('Đặt lại đơn này')
                    ->requiresConfirmation()
                    ->modalHeading('Đặt lại đơn hàng')
                    ->modalDescription('Một đơn hàng mới sẽ được tạo với toàn bộ sản phẩm và thông tin khách hàng của đơn này. Ngày đặt, mã đơn hàng và trạng thái sẽ được đặt lại.')
                    ->modalSubmitActionLabel('Tạo đơn mới')
                    ->action(function ($record): void {
                        DB::transaction(function () use ($record): void {
                            $newOrder = $record->replicate([
                                'uuid',
                                'ordered_at',
                                'status',
                                'created_at',
                                'updated_at',
                            ]);

                            $newOrder->uuid = (string) Str::uuid();
                            $newOrder->ordered_at = now();
                            $newOrder->status = 'new';
                            $newOrder->save();

                            foreach ($record->items as $item) {
                                $newOrder->items()->create([
                                    'product_id' => $item->product_id,
                                    'quantity' => $item->quantity,
                                    'total_unit_price' => $item->total_unit_price,
                                ]);
                            }
                        });
                    })
                    ->successNotificationTitle('Đã tạo đơn hàng mới'),

                Action::make('print')
                    ->icon(Heroicon::OutlinedPrinter)
                    ->iconButton()
                    ->tooltip('In đơn hàng')
                    ->url(fn ($record): string => route('orders.print', $record))
                    ->openUrlInNewTab(),
            ], RecordActionsPosition::BeforeColumns)
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
