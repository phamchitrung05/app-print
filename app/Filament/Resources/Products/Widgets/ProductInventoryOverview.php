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

        // Thống kê riêng cho sản phẩm in ly
        $mugInventory = ProductSKU::query()
            ->join('products', 'product_skus.product_id', '=', 'products.id')
            ->where('products.product_type', 'in_ly')
            ->select([
                DB::raw('COALESCE(SUM(product_skus.price * product_skus.stock), 0) as total_value'),
                DB::raw('COALESCE(SUM(product_skus.stock), 0) as total_stock'),
            ])
            ->first();

        $mugTotalValue = (float) ($mugInventory->total_value ?? 0);
        $mugTotalStock = (int) ($mugInventory->total_stock ?? 0);

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

            Stat::make(
                'Tồn kho in ly',
                number_format($mugTotalStock, 0, ',', '.') . ' sản phẩm',
            )
                ->description('Giá trị: ' . number_format($mugTotalValue, 0, ',', '.') . ' ₫')
                ->color('warning')
                ->icon('heroicon-o-fire'),
        ];
    }
}
