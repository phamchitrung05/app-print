<?php

namespace App\Filament\Resources\CustomerStocks;

use App\Filament\Resources\CustomerStocks\Pages\ListCustomerStocks;
use App\Filament\Resources\CustomerStocks\Schemas\CustomerStockForm;
use App\Filament\Resources\CustomerStocks\Tables\CustomerStocksTable;
use App\Models\CustomerStock;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CustomerStockResource extends Resource
{
    protected static ?string $model = CustomerStock::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBoxArrowDown;

    protected static string|\UnitEnum|null $navigationGroup = 'Tồn Kho';

    protected static ?string $navigationLabel = 'Tồn kho Sẵn Hàng';

    protected static ?string $modelLabel = 'tồn kho sẵn hàng';

    protected static ?string $pluralModelLabel = 'tồn kho sẵn hàng';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return CustomerStockForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomerStocksTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCustomerStocks::route('/'),
        ];
    }
}
