<?php

namespace App\Filament\Resources\Products\Widgets;

use App\Models\ProductSKU;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class ProductInventoryOverview extends StatsOverviewWidget
{
    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $inventory = ProductSKU::query()
            ->select([
                DB::raw('COALESCE(SUM(price * stock), 0) as total_value'),
                DB::raw('COALESCE(SUM(stock), 0) as total_stock'),
            ])
            ->first();

        $totalValue = (float) ($inventory->total_value ?? 0);
        $totalStock = (int) ($inventory->total_stock ?? 0);

        return [
            Stat::make(
                'Giá trị hàng tồn kho',
                number_format($totalValue, 0, ',', '.') . ' ₫',
            )
                ->description('Tổng giá trị theo giá bán của các SKU')
                ->color('success')
                ->icon('heroicon-o-banknotes'),

            Stat::make(
                'Tổng số lượng tồn kho',
                number_format($totalStock, 0, ',', '.'),
            )
                ->description('Tổng số sản phẩm còn trong kho')
                ->color('primary')
                ->icon('heroicon-o-cube'),
        ];
    }
}
