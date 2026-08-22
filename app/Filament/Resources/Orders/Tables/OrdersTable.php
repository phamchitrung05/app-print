<?php

namespace App\Filament\Resources\Orders\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Js;
use Illuminate\Support\Str;
use App\Filament\Resources\Orders\Schemas\OrdersForm;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn (Builder $query): Builder => $query
                    ->with(['customer', 'items'])
                    ->orderByRaw("CASE WHEN status = 'cancelled' THEN 2 WHEN status = 'completed' THEN 1 ELSE 0 END")
                    ->orderByDesc('ordered_at')
            )
            ->columns([
                TextColumn::make('code')
                    ->label('Mã đơn hàng')
                    ->weight(FontWeight::Bold)
                    ->searchable()
                    ->sortable()
                    ->copyable(),

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
                    ->disabled(fn ($record): bool => in_array($record?->status, ['completed', 'cancelled'], true))
                    ->selectablePlaceholder(false)
                    ->sortable(),

                TextColumn::make('payment_method')
                    ->label('PT thanh toán')
                    ->formatStateUsing(
                        fn (?string $state): ?string => config('orders.payment_methods')[$state] ?? $state
                    )
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

                TernaryFilter::make('ready_made_goods')
                    ->label('Đơn làm sẵn')
                    ->placeholder('Tất cả')
                    ->trueLabel('Làm sẵn')
                    ->falseLabel('Đặt làm')
                    ->queries(
                        true: fn (Builder $query) => $query->where('ready_made_goods', true),
                        false: fn (Builder $query) => $query->where('ready_made_goods', false),
                    ),

                SelectFilter::make('payment_method')
                    ->label('Phương thức thanh toán')
                    ->options(config('orders.payment_methods')),
            ])
            ->recordActions([
                ViewAction::make()
                    ->iconButton()
                    ->modalHeading(fn ($record): string => "Chi tiết đơn hàng: {$record->code}")
                    ->schema(fn (Schema $schema): Schema => OrdersForm::configure($schema))
                    ->modalWidth('7xl'),

                EditAction::make()
                    ->iconButton()
                    ->hidden(fn ($record): bool => in_array($record?->status, ['completed', 'cancelled'], true)),

                Action::make('reorder')
                    ->label('Đặt lại đơn này')
                    ->icon(Heroicon::OutlinedArrowPath)
                    ->iconButton()
                    ->requiresConfirmation()
                    ->modalHeading('Đặt lại đơn hàng')
                    ->modalDescription('Một đơn hàng mới sẽ được tạo với toàn bộ sản phẩm và thông tin khách hàng của đơn này. Ngày đặt, mã đơn hàng và trạng thái sẽ được đặt lại.')
                    ->modalSubmitActionLabel('Tạo đơn mới')
                    ->action(function ($record): void {
                        DB::transaction(function () use ($record): void {
                            $newOrder = $record->replicate([
                                'uuid',
                                'code',
                                'ordered_at',
                                'status',
                                'status_paid',
                                'inventory_deducted_at',
                                'created_at',
                                'updated_at',
                            ]);

                            $newOrder->uuid = (string) Str::uuid();
                            $newOrder->code = null;
                            $newOrder->ordered_at = now();
                            $newOrder->status = 'new';
                            $newOrder->status_paid = 'unpaid';
                            $newOrder->inventory_deducted_at = null;
                            $newOrder->save();

                            foreach ($record->items as $item) {
                                $newOrder->items()->create([
                                    'product_sku_id' => $item->product_sku_id,
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
                    ->hidden(fn ($record): bool => $record?->status === 'cancelled')
                    ->url(fn ($record): string => route('orders.print', $record))
                    ->openUrlInNewTab(),

                DeleteAction::make()
                    ->iconButton()
                    ->hidden(fn (): bool => true),
            ], RecordActionsPosition::BeforeColumns)
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('print')
                        ->label('In các đơn đã chọn')
                        ->icon(Heroicon::OutlinedPrinter)
                        ->action(function (Collection $records, $livewire): void {
                            $printable = $records->reject(fn ($order): bool => $order->status === 'cancelled');

                            if ($printable->isEmpty()) {
                                Notification::make()
                                    ->title('Không thể in đơn đã hủy')
                                    ->body('Các đơn đã chọn đều ở trạng thái Đã Hủy.')
                                    ->warning()
                                    ->send();

                                return;
                            }

                            if ($printable->count() !== $records->count()) {
                                Notification::make()
                                    ->title('Đã loại đơn hủy khỏi danh sách in')
                                    ->body('Các đơn ở trạng thái Đã Hủy sẽ không được in.')
                                    ->warning()
                                    ->send();
                            }

                            $url = route(
                                'orders.print.bulk',
                                ['ids' => $printable->pluck('id')->implode(',')],
                            );

                            $livewire->js("window.open(" . Js::from($url) . ", '_blank')");
                        }),

                    DeleteBulkAction::make()
                        ->hidden(fn (): bool => true),
                ]),
            ]);
    }
}
