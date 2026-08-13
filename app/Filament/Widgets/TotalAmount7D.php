<?php

namespace App\Filament\Widgets;

use App\Models\Orders;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class TotalAmount7D extends StatsOverviewWidget
{
    protected int | string | array $columnSpan = 1;

    protected function getStats(): array
    {
        $startDate = Carbon::now()->startOfWeek()->startOfDay();
        $endDate = Carbon::now()->endOfWeek()->endOfDay();

        $totalAmount = Orders::query()
            ->where('status', 'completed')
            ->whereBetween('ordered_at', [$startDate, $endDate])
            ->sum('total_price');

        return [
            Stat::make(
                'Tổng tiền đơn hoàn thành trong tuần',
                number_format((float) $totalAmount, 0, ',', '.') . ' ₫',
            )
                ->description('Từ ' . $startDate->format('d/m/Y') . ' đến ' . $endDate->format('d/m/Y'))
                ->color('success'),
        ];
    }
}
