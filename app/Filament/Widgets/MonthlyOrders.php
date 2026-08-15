<?php

namespace App\Filament\Widgets;

use App\Models\Orders;
use Filament\Support\Colors\Color;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class MonthlyOrders extends ChartWidget
{
    protected ?string $heading = 'Số đơn hoàn thành theo tháng';

    protected ?string $description = 'Thống kê đơn hàng đã hoàn thành trong 12 tháng gần nhất';

    protected int | string | array $columnSpan = 3;

    protected ?string $maxHeight = '360px';

    protected function getData(): array
    {
        $firstMonth = Carbon::now()->startOfMonth()->subMonths(11);
        $labels = [];
        $orderCounts = [];
        $monthlyAmounts = [];

        for ($month = 0; $month < 12; $month++) {
            $monthDate = $firstMonth->copy()->addMonths($month);

            $labels[] = $monthDate->format('m/Y');
            $ordersQuery = Orders::query()
                ->where('status', 'completed')
                ->whereBetween('ordered_at', [
                    $monthDate->copy()->startOfMonth(),
                    $monthDate->copy()->endOfMonth(),
                ]);

            $orderCounts[] = (clone $ordersQuery)->count();
            $monthlyAmounts[] = (float) (clone $ordersQuery)->sum('total_price');
        }

        return [
            'datasets' => [
                [
                    'label' => 'Đơn hàng hoàn thành',
                    'data' => $orderCounts,
                    'backgroundColor' => Color::Blue[500],
                    'hoverBackgroundColor' => Color::Blue[600],
                    'borderColor' => Color::Blue[700],
                    'borderWidth' => 1,
                    'borderRadius' => 8,
                    'borderSkipped' => false,
                    'barPercentage' => 0.7,
                    'categoryPercentage' => 0.8,
                ],
                [
                    'label' => 'Tổng tiền đơn hoàn thành',
                    'data' => $monthlyAmounts,
                    'backgroundColor' => Color::Blue[200],
                    'hoverBackgroundColor' => Color::Blue[300],
                    'borderColor' => Color::Blue[500],
                    'borderWidth' => 1,
                    'borderRadius' => 8,
                    'borderSkipped' => false,
                    'barPercentage' => 0.7,
                    'categoryPercentage' => 0.8,
                    'yAxisID' => 'y1',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'interaction' => [
                'mode' => 'index',
                'intersect' => false,
            ],
            'plugins' => [
                'legend' => [
                    'position' => 'top',
                    'align' => 'end',
                    'labels' => [
                        'usePointStyle' => true,
                        'pointStyle' => 'circle',
                        'boxWidth' => 8,
                        'boxHeight' => 8,
                        'padding' => 20,
                    ],
                ],
            ],
            'scales' => [
                'x' => [
                    'grid' => [
                        'display' => false,
                    ],
                    'border' => [
                        'display' => false,
                    ],
                    'ticks' => [
                        'color' => '#6b7280',
                    ],
                ],
                'y' => [
                    'beginAtZero' => true,
                    'grace' => '10%',
                    'grid' => [
                        'color' => 'rgba(107, 114, 128, 0.15)',
                    ],
                    'border' => [
                        'display' => false,
                    ],
                    'ticks' => [
                        'precision' => 0,
                        'stepSize' => 1,
                        'color' => '#6b7280',
                    ],
                ],
                'y1' => [
                    'beginAtZero' => true,
                    'position' => 'right',
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                    'border' => [
                        'display' => false,
                    ],
                    'ticks' => [
                        'color' => Color::Blue[500],
                        'callback' => 'function(value) { return new Intl.NumberFormat("vi-VN").format(value) + " ₫"; }',
                    ],
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
