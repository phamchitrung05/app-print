<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Customer;
use App\Models\Orders;
use App\Models\Product;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

class OrdersForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Thông tin đơn hàng')
                    ->icon(Heroicon::OutlinedClipboardDocumentList)
                    ->schema([
                        TextInput::make('uuid')
                            ->label('Mã đơn hàng')
                            ->default(fn (): string => (string) Str::uuid())
                            ->disabled()
                            ->dehydrated()
                            ->helperText('Mã định danh duy nhất được hệ thống tự động tạo.'),

                        DateTimePicker::make('ordered_at')
                            ->label('Ngày đặt hàng')
                            ->required()
                            ->default(now())
                            ->seconds(false)
                            ->native(false),

                        Select::make('status')
                            ->label('Trạng thái')
                            ->required()
                            ->default('new')
                            ->options([
                                'new' => 'Mới tạo',
                                'confirmed' => 'Đã xác nhận',
                                'processing' => 'Đang xử lý',
                                'completed' => 'Hoàn thành',
                                'cancelled' => 'Đã hủy',
                            ]),

                        Select::make('customer_id')
                            ->label('Khách hàng')
                            ->relationship('customer', 'name')
                            ->getOptionLabelFromRecordUsing(
                                fn (Customer $record): string => "{$record->name} — {$record->phone}"
                            )
                            ->searchable(['name', 'phone'])
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (?string $state, Set $set): void {
                                $customer = Customer::find($state);

                                $set('phone', $customer?->phone);
                                $set('address', $customer?->address);
                            }),

                        TextInput::make('phone')
                            ->label('Số điện thoại')
                            ->tel()
                            ->formatStateUsing(fn (?Orders $record): ?string => $record?->customer?->phone)
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Tự động điền khi chọn khách hàng'),

                        Select::make('payment_method')
                            ->label('Phương thức thanh toán')
                            ->required()
                            ->default('cash')
                            ->options([
                                'cash' => 'Tiền mặt',
                                'bank_transfer' => 'Chuyển khoản',
                                'card' => 'Thẻ',
                                'e_wallet' => 'Ví điện tử',
                            ]),

                        TextInput::make('address')
                            ->label('Địa chỉ')
                            ->formatStateUsing(fn (?Orders $record): ?string => $record?->customer?->address)
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Tự động điền khi chọn khách hàng'),

                        Textarea::make('note')
                            ->label('Ghi chú')
                            ->rows(4)
                            ->maxLength(65535)
                            ->placeholder('Nhập ghi chú cho đơn hàng...')
                            ->columnSpanFull(),
                    ])
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                        'xl' => 3,
                    ])
                    ->columnSpanFull(),

                Section::make('Sản phẩm trong đơn hàng')
                    ->icon(Heroicon::OutlinedShoppingCart)
                    ->schema([
                        Repeater::make('items')
                            ->relationship()
                            ->table([
                                TableColumn::make('Sản phẩm')
                                    ->width('40%')
                                    ->markAsRequired(),

                                TableColumn::make('Số lượng')
                                    ->width('15%')
                                    ->markAsRequired(),

                                TableColumn::make('Đơn giá')
                                    ->width('20%')
                                    ->markAsRequired(),

                                TableColumn::make('Thành tiền')
                                    ->width('20%'),
                            ])
                            ->schema([
                                Select::make('product_id')
                                    ->label('Sản phẩm')
                                    ->relationship('product', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function (?string $state, Set $set): void {
                                        $set('total_unit_price', Product::find($state)?->price ?? 0);
                                    }),

                                TextInput::make('quantity')
                                    ->label('Số lượng')
                                    ->required()
                                    ->numeric()
                                    ->integer()
                                    ->minValue(1)
                                    ->default(1)
                                    ->live(),

                                TextInput::make('total_unit_price')
                                    ->label('Đơn giá')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0)
                                    ->prefix('₫')
                                    ->live(),

                                Placeholder::make('line_total')
                                    ->label('Thành tiền')
                                    ->content(
                                        fn (Get $get): string => number_format(
                                            (float) ($get('quantity') ?? 0) * (float) ($get('total_unit_price') ?? 0),
                                            0,
                                            ',',
                                            '.',
                                        ) . ' ₫'
                                    )
                                    ->dehydrated(false),
                            ])
                            ->defaultItems(1)
                            ->addActionLabel('Thêm sản phẩm')
                            ->reorderable(false)
                            ->columnSpanFull(),

                        Placeholder::make('order_total')
                            ->label('Tổng tiền')
                            ->content(function (Get $get): string {
                                $total = collect($get('items') ?? [])
                                    ->sum(
                                        fn (array $item): float => (float) ($item['quantity'] ?? 0)
                                            * (float) ($item['total_unit_price'] ?? 0)
                                    );

                                return number_format($total, 0, ',', '.') . ' ₫';
                            })
                            ->extraAttributes([
                                'class' => 'text-xl font-bold text-primary-600',
                            ])
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
