<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->schema([
                        Grid::make(1)
                            ->schema([
                                Section::make('Thông tin cơ bản')
                                    ->icon(Heroicon::OutlinedInformationCircle)
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Tên sản phẩm')
                                            ->required()
                                            ->maxLength(255)
                                            ->autofocus()
                                            ->placeholder('Nhập tên sản phẩm'),

                                        Select::make('product_type')
                                            ->label('Loại sản phẩm')
                                            ->options([
                                                'in_ly' => 'In ly',
                                                'in_giay' => 'In giấy',
                                            ])
                                            ->default('in_ly')
                                            ->required()
                                            ->native(false),

                                        TextInput::make('uuid')
                                            ->label('Mã sản phẩm (UUID)')
                                            ->default(fn (): string => (string) Str::uuid())
                                            ->disabled()
                                            ->dehydrated()
                                            ->helperText('Mã định danh duy nhất được hệ thống tự động tạo.'),

                                        TextInput::make('unit')
                                            ->label('Đơn vị tính')
                                            ->required()
                                            ->maxLength(255)
                                            ->placeholder('Ví dụ: cái, hộp, kg'),
                                    ])
                                    ->columns(2),

                                Section::make('Phân Loại Sản Phẩm')
                                    ->schema([
                                        Repeater::make('product_skus')
                                            ->label('Danh sách SKU')
                                            ->schema([
                                                TextInput::make('sku')
                                                    ->label('SKU')
                                                    ->required()
                                                    ->maxLength(100)
                                                    ->placeholder('Nhập mã SKU'),
                                                TextInput::make('price')
                                                    ->label('Giá bán')
                                                    ->required()
                                                    ->numeric()
                                                    ->minValue(0)
                                                    ->step(0.01)
                                                    ->suffix('₫')
                                                    ->placeholder('Nhập giá bán'),

                                                TextInput::make('stock')
                                                    ->label('Tồn kho')
                                                    ->required()
                                                    ->integer()
                                                    ->minValue(0)
                                                    ->default(0)
                                                    ->placeholder('Nhập số lượng tồn kho'),
                                            ])
                                            ->columns(2)
                                            ->addActionLabel('Thêm SKU')
                                            ->reorderable()
                                            ->defaultItems(1)
                                            ->minItems(1)
                                            ->collapsible()
                                            ->columnSpanFull(),
                                    ]),
                            ])
                            ->columnSpan(['default' => 3, 'lg' => 2]),

                        Section::make('Thông tin khác')
                            ->icon(Heroicon::OutlinedCog6Tooth)
                            ->schema([
                                Toggle::make('is_active')
                                    ->label('Trạng thái hoạt động')
                                    ->onColor('primary')
                                    ->default(true)
                                    ->required(),

                                Textarea::make('internal_note')
                                    ->label('Ghi chú nội bộ')
                                    ->rows(5)
                                    ->maxLength(65535)
                                    ->placeholder('Nhập ghi chú nội bộ...'),
                            ])
                            ->columnSpan(['default' => 3, 'lg' => 1]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
