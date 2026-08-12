<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

class CustomerForm
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
                                            ->label('Họ và tên')
                                            ->required()
                                            ->maxLength(255)
                                            ->autofocus()
                                            ->placeholder('Nhập họ và tên khách hàng'),

                                        TextInput::make('uuid')
                                            ->label('Mã khách hàng (UUID)')
                                            ->default(fn (): string => (string) Str::uuid())
                                            ->disabled()
                                            ->dehydrated()
                                            ->helperText('Mã định danh duy nhất được hệ thống tự động tạo.'),

                                        TextInput::make('phone')
                                            ->label('Số điện thoại')
                                            ->required()
                                            ->tel()
                                            ->maxLength(30)
                                            ->placeholder('Nhập số điện thoại'),
                                    ])
                                    ->columns(2),

                                Section::make('Thông tin bổ sung')
                                    ->icon(Heroicon::OutlinedDocumentText)
                                    ->schema([
                                        Textarea::make('note')
                                            ->label('Ghi chú')
                                            ->rows(5)
                                            ->maxLength(65535)
                                            ->placeholder('Nhập ghi chú về khách hàng...'),

                                        KeyValue::make('option')
                                            ->label('Tùy chọn')
                                            ->keyLabel('Tên tùy chọn')
                                            ->valueLabel('Giá trị')
                                            ->addActionLabel('Thêm tùy chọn')
                                            ->reorderable(),
                                    ])
                                    ->columns(2),
                            ])
                            ->columnSpan(['default' => 3, 'lg' => 2]),

                        Grid::make(1)
                            ->schema([
                                Section::make('Địa chỉ')
                                    ->icon(Heroicon::OutlinedMapPin)
                                    ->schema([
                                        Textarea::make('address')
                                            ->label('Địa chỉ')
                                            ->rows(6)
                                            ->maxLength(65535)
                                            ->placeholder('Nhập địa chỉ chi tiết'),
                                    ]),

                                Section::make('Trạng thái')
                                    ->icon(Heroicon::OutlinedCog6Tooth)
                                    ->schema([
                                        Toggle::make('is_active')
                                            ->label('Hoạt động')
                                            ->helperText('Khách hàng có thể đặt hàng và sử dụng dịch vụ.')
                                            ->onColor('primary')
                                            ->default(true)
                                            ->required(),
                                    ]),
                            ])
                            ->columnSpan(['default' => 3, 'lg' => 1]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
