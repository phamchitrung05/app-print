<?php

namespace App\Filament\Resources\Payments\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Thông tin thanh toán')
                    ->schema([
                        Select::make('order_id')
                            ->label('Đơn hàng')
                            ->relationship('order', 'code')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('payment_status')
                            ->label('Trạng thái thanh toán')
                            ->options(config('orders.payment_statuses'))
                            ->default('pending')
                            ->selectablePlaceholder(false)
                            ->required(),

                        Select::make('confirmed_by')
                            ->label('Người xác nhận')
                            ->relationship('confirmedBy', 'name')
                            ->disabled()
                            ->dehydrated(false),

                        DateTimePicker::make('confirmed_at')
                            ->label('Thời điểm xác nhận')
                            ->seconds(false)
                            ->native(false)
                            ->disabled()
                            ->dehydrated(false),
                    ])
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
