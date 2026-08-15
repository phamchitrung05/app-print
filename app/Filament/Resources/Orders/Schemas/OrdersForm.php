<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Customer;
use App\Models\Orders;
use App\Models\Product;
use App\Models\ProductSKU;
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
use Illuminate\Database\Eloquent\Builder;
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
                            ->default(fn (): string => (string) Str::uuid())
                            ->hidden()
                            ->dehydrated(),

                        TextInput::make('code')
                            ->label('Mã đơn hàng')
                            ->formatStateUsing(
                                fn (?string $state): string => $state ?? 'Tự động sau khi lưu'
                            )
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText('Mã gồm 5 chữ số, được hệ thống tự động tạo theo thứ tự đơn hàng.'),

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
                            ->options(config('orders.statuses'))
                            ->selectablePlaceholder(false),

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
                            ->options(config('orders.payment_methods'))
                            ->default('cash')
                            ->selectablePlaceholder(false)
                            ->required(),

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

                                TableColumn::make('SKU')
                                    ->width('20%')
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
                                    ->dehydrated(false)
                                    ->options(function (Get $get): array {
                                        $items = $get('../../items') ?? [];
                                        $currentProductId = $get('product_id');
                                        $selectedSkuIds = collect($items)
                                            ->pluck('product_sku_id')
                                            ->filter()
                                            ->map(fn ($id): int => (int) $id)
                                            ->reject(fn (int $id): bool => $id === (int) ($get('product_sku_id') ?? 0))
                                            ->values();

                                        return Product::query()
                                            ->with('skus')
                                            ->orderBy('name')
                                            ->get()
                                            ->filter(function (Product $product) use ($selectedSkuIds, $currentProductId): bool {
                                                return (string) $product->id === (string) $currentProductId
                                                    || $product->skus->contains(
                                                        fn (ProductSKU $sku): bool => ! $selectedSkuIds->contains($sku->id)
                                                    );
                                            })
                                            ->pluck('name', 'id')
                                            ->all();
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateHydrated(function (Select $component, Get $get): void {
                                        $productSkuId = $get('product_sku_id');

                                        if ($productSkuId) {
                                            $component->state(
                                                ProductSKU::find($productSkuId)?->product_id
                                            );
                                        }
                                    })
                                    ->afterStateUpdated(function (?string $state, Set $set, Get $get): void {
                                        $items = $get('../../items') ?? [];
                                        $selectedSkuIds = collect($items)
                                            ->pluck('product_sku_id')
                                            ->filter()
                                            ->map(fn ($id): int => (int) $id)
                                            ->reject(fn (int $id): bool => $id === (int) ($get('product_sku_id') ?? 0))
                                            ->values();

                                        $firstSku = ProductSKU::query()
                                            ->where('product_id', $state)
                                            ->whereNotIn('id', $selectedSkuIds)
                                            ->orderBy('sku')
                                            ->first();

                                        $set('product_sku_id', $firstSku?->id);
                                        $set('total_unit_price', $firstSku?->price ?? 0);
                                    }),

                                Select::make('product_sku_id')
                                    ->label('SKU')
                                    ->options(function (Get $get): array {
                                        $items = $get('../../items') ?? [];
                                        $currentSkuId = (int) ($get('product_sku_id') ?? 0);
                                        $selectedSkuIds = collect($items)
                                            ->pluck('product_sku_id')
                                            ->filter()
                                            ->map(fn ($id): int => (int) $id)
                                            ->reject(fn (int $id): bool => $id === $currentSkuId)
                                            ->values();

                                        return ProductSKU::query()
                                            ->where('product_id', $get('product_id'))
                                            ->whereNotIn('id', $selectedSkuIds)
                                            ->orderBy('sku')
                                            ->pluck('sku', 'id')
                                            ->all();
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->selectablePlaceholder(false)
                                    ->live()
                                    ->afterStateUpdated(function (?string $state, Set $set): void {
                                        $set('total_unit_price', ProductSKU::find($state)?->price ?? 0);
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

                        Grid::make([
                            'default' => 1,
                            'md' => 3,
                        ])
                            ->schema([
                                TextInput::make('discount')
                                    ->label('Chiết khấu')
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0)
                                    ->prefix('₫')
                                    ->live()
                                    ->helperText('Số tiền được giảm trực tiếp trên đơn hàng.'),

                                Placeholder::make('order_subtotal')
                                    ->label('Tổng tiền hàng')
                                    ->content(function (Get $get): string {
                                        $subtotal = collect($get('items') ?? [])
                                            ->sum(
                                                fn (array $item): float => (float) ($item['quantity'] ?? 0)
                                                    * (float) ($item['total_unit_price'] ?? 0)
                                            );

                                        return number_format($subtotal, 0, ',', '.') . ' ₫';
                                    }),

                                Placeholder::make('order_total')
                                    ->label('Tổng thanh toán')
                                    ->content(function (Get $get): string {
                                        $subtotal = collect($get('items') ?? [])
                                            ->sum(
                                                fn (array $item): float => (float) ($item['quantity'] ?? 0)
                                                    * (float) ($item['total_unit_price'] ?? 0)
                                            );
                                        $discount = max(0, (float) ($get('discount') ?? 0));

                                        return number_format(max(0, $subtotal - $discount), 0, ',', '.') . ' ₫';
                                    })
                                    ->extraAttributes([
                                        'class' => 'text-xl font-bold text-primary-600',
                                    ]),
                            ])
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
