<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class TotalActiveCustomers extends StatsOverviewWidget
{
    protected int | string | array $columnSpan = 1;

    protected function getStats(): array
    {
        $now = Carbon::now();
        $currentMonthStart = $now->copy()->subMonth();
        $previousMonthStart = $currentMonthStart->copy()->subMonth();

        $activeCustomers = Customer::query()
            ->where('is_active', true)
            ->count();

        $currentMonthCount = Customer::query()
            ->where('is_active', true)
            ->whereBetween('created_at', [$currentMonthStart, $now])
            ->count();

        $previousMonthCount = Customer::query()
            ->where('is_active', true)
            ->whereBetween('created_at', [$previousMonthStart, $currentMonthStart])
            ->count();

        $change = $currentMonthCount - $previousMonthCount;
        $changeLabel = $change > 0
            ? 'Tăng ' . $change . ' khách hàng so với tháng trước'
            : ($change < 0
                ? 'Giảm ' . abs($change) . ' khách hàng so với tháng trước'
                : 'Không thay đổi so với tháng trước');

        return [
            Stat::make(
                'Tổng khách hàng đang hoạt động',
                number_format($activeCustomers),
            )
                ->description($changeLabel)
                ->descriptionIcon($change > 0 ? 'heroicon-m-arrow-trending-up' : ($change < 0 ? 'heroicon-m-arrow-trending-down' : 'heroicon-m-minus'))
                ->color($change > 0 ? 'success' : ($change < 0 ? 'danger' : 'gray')),
        ];
    }
}
