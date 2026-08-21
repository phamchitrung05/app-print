<?php

namespace App\Filament\Resources\CustomerStocks\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class CustomerStockForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->schema([
                        Section::make('Thông tin tồn kho')
                            ->icon(Heroicon::OutlinedArchiveBox)
                            ->schema([
                                Select::make('customer_id')
                                    ->label('Khách hàng')
                                    ->relationship('customer', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->native(false),

                                Select::make('product_sku_id')
                                    ->label('SKU sản phẩm')
                                    ->relationship('productSku', 'sku')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->native(false),

                                Select::make('order_item_id')
                                    ->label('Order Item')
                                    ->relationship('orderItem', 'id')
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('Không liên kết')
                                    ->helperText('1 order_item chỉ có 1 customer_stock'),

                                TextInput::make('quantity')
                                    ->label('Số lượng')
                                    ->required()
                                    ->integer()
                                    ->minValue(0)
                                    ->default(0),

                                Select::make('status')
                                    ->label('Trạng thái')
                                    ->options([
                                        'pending' => 'Chờ xử lý',
                                        'in_stock' => 'Còn hàng',
                                        'out_of_stock' => 'Hết hàng',
                                        'reserved' => 'Đã đặt trước',
                                    ])
                                    ->default('pending')
                                    ->required()
                                    ->native(false),

                                Textarea::make('note')
                                    ->label('Ghi chú')
                                    ->rows(3)
                                    ->maxLength(65535)
                                    ->placeholder('Nhập ghi chú...')
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
