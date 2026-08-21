<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Filament\Resources\Products\Widgets\ProductInventoryOverview;
use App\Models\OrderItem;
use App\Models\ProductSKU;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ProductStock extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = ProductResource::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;

    protected static ?string $navigationLabel = 'Tồn kho';

    protected static bool $shouldRegisterNavigation = false;

    protected static string|\UnitEnum|null $navigationGroup = 'Tồn Kho';

    protected static ?string $title = 'Tồn kho sản phẩm';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.resources.products.pages.product-stock';

    protected function getHeaderWidgets(): array
    {
        return [
            ProductInventoryOverview::class,
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(ProductSKU::query()->with('product'))
            ->columns([
                TextColumn::make('product.name')
                    ->label('Sản phẩm')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('sku')
                    ->label('Mã SKU')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('price')
                    ->label('Giá')
                    ->money('VND', locale: 'vi')
                    ->sortable(),

                TextColumn::make('stock')
                    ->label('Tồn kho')
                    ->numeric(locale: 'vi')
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state <= 0 => 'danger',
                        $state <= 10 => 'warning',
                        default => 'success',
                    })
                    ->sortable(),

                TextColumn::make('product.unit')
                    ->label('Đơn vị')
                    ->placeholder('—'),

                TextColumn::make('updated_at')
                    ->label('Cập nhật lúc')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('stock_status')
                    ->label('Trạng thái tồn kho')
                    ->placeholder('Tất cả SKU')
                    ->trueLabel('Hết hàng')
                    ->falseLabel('Còn hàng')
                    ->queries(
                        true: fn ($query) => $query->where('stock', 0),
                        false: fn ($query) => $query->where('stock', '>', 0),
                    ),
            ])
            ->recordClasses(fn (ProductSKU $record): string => (int) $record->stock === 0
                ? 'bg-danger-50 dark:bg-danger-950/20'
                : '')
            ->recordActions([
                Action::make('statistics')
                    ->label('Thống kê')
                    ->icon(Heroicon::OutlinedChartBar)
                    ->iconButton()
                    ->color('info')
                    ->modalHeading(fn (ProductSKU $record): string => "Thống kê SKU: {$record->sku}")
                    ->modalDescription('Số liệu bán hàng của các đơn đã hoàn thành')
                    ->modalWidth('7xl')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Đóng')
                    ->modalContent(fn (ProductSKU $record): View => view(
                        'filament.resources.products.modals.sku-sales-statistics',
                        $this->getSkuSalesStatistics($record),
                    )),

                EditAction::make()
                    ->iconButton()
                    ->icon(Heroicon::OutlinedPencilSquare)
                    ->modalHeading(fn (ProductSKU $record): string => "Cập nhật tồn kho: {$record->sku}")
                    ->form([
                        TextInput::make('stock')
                            ->label('Tồn kho')
                            ->required()
                            ->integer()
                            ->minValue(0)
                            ->autofocus(),
                    ])
                    ->using(function (ProductSKU $record, array $data): ProductSKU {
                        $record->update([
                            'stock' => (int) $data['stock'],
                        ]);

                        return $record;
                    }),
            ])
            ->defaultSort('stock')
            ->paginated([10, 25, 50, 100]);
    }

    /**
     * @return array<string, mixed>
     */
    private function getSkuSalesStatistics(ProductSKU $sku): array
    {
        $now = now();
        $monthStart = $now->copy()->startOfMonth();
        $monthEnd = $now->copy()->endOfMonth();

        $items = OrderItem::query()
            ->with(['order.customer'])
            ->where('product_sku_id', $sku->getKey())
            ->whereHas('order', fn ($query) => $query
                ->where('status', 'completed')
                ->whereBetween('ordered_at', [$monthStart, $monthEnd]))
            ->get();

        $totalQuantity = (int) $items->sum('quantity');
        $totalRevenue = (float) $items->sum(
            fn (OrderItem $item): float => $item->quantity * (float) $item->total_unit_price
        );
        $totalOrders = $items->pluck('order_id')->unique()->count();

        $topOrders = $items
            ->map(fn (OrderItem $item): array => [
                'code' => $item->order?->code ?? '—',
                'ordered_at' => $item->order?->ordered_at,
                'customer' => $item->order?->customer?->name ?? 'Khách lẻ',
                'quantity' => $item->quantity,
                'revenue' => $item->quantity * (float) $item->total_unit_price,
            ])
            ->sortByDesc('revenue')
            ->take(5)
            ->values();

        $sixMonthStart = $now->copy()->subMonths(5)->startOfMonth();

        $monthlyItems = OrderItem::query()
            ->with('order')
            ->where('product_sku_id', $sku->getKey())
            ->whereHas('order', fn ($query) => $query
                ->where('status', 'completed')
                ->whereBetween('ordered_at', [$sixMonthStart, $monthEnd]))
            ->get();

        $itemsByMonth = $monthlyItems->groupBy(
            fn (OrderItem $item): string => $item->order->ordered_at->format('Y-m')
        );

        $monthlyStatistics = collect(range(0, 5))
            ->map(function (int $monthsAgo) use ($now, $itemsByMonth): array {
                $month = $now->copy()->subMonths($monthsAgo);
                $monthItems = $itemsByMonth->get($month->format('Y-m'), collect());

                return [
                    'month' => $month->format('m/Y'),
                    'quantity' => (int) $monthItems->sum('quantity'),
                    'revenue' => (float) $monthItems->sum(
                        fn (OrderItem $item): float => $item->quantity * (float) $item->total_unit_price
                    ),
                    'orders' => $monthItems->pluck('order_id')->unique()->count(),
                ];
            });

        return [
            'sku' => $sku->loadMissing('product'),
            'monthLabel' => Carbon::parse($monthStart)->translatedFormat('F Y'),
            'totalQuantity' => $totalQuantity,
            'totalRevenue' => $totalRevenue,
            'totalOrders' => $totalOrders,
            'topOrders' => $topOrders,
            'monthlyStatistics' => $monthlyStatistics,
        ];
    }
}
