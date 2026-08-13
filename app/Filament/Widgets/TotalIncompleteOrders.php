<?php

namespace App\Filament\Widgets;

use App\Models\Orders;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TotalIncompleteOrders extends StatsOverviewWidget
{
    protected int | string | array $columnSpan = 1;
    protected function getStats(): array
    {
        $totalOrders = Orders::query()->count();
        $incompleteOrders = Orders::query()
            ->where('status', '!=', 'completed')
            ->count();

        return [
            Stat::make(
                'Đơn hàng chưa hoàn thành',
                "{$incompleteOrders}",
            )
                ->description('Số đơn chưa hoàn thành')
                ->color('warning'),
        ];
    }
}
