<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\IncompleteOrdersTable;
use App\Filament\Widgets\MonthlyOrders;
use App\Filament\Widgets\TotalActiveCustomers;
use App\Filament\Widgets\TotalAmount7D;
use App\Filament\Widgets\TotalIncompleteOrders;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{

    protected static ?string $title = 'Trang Chủ';

    public function getColumns(): int | array
    {
        return 3;
    }

    public function getWidgets(): array
    {
        return [
            TotalAmount7D::class,
            TotalActiveCustomers::class,
            TotalIncompleteOrders::class,
            MonthlyOrders::class,
            IncompleteOrdersTable::class,
        ];
    }
}
