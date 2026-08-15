<?php

namespace App\Filament\Resources\Shippings;

use App\Filament\Resources\Shippings\Pages\ListShippings;
use App\Filament\Resources\Shippings\Tables\ShippingsTable;
use App\Models\Shipping;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ShippingResource extends Resource
{
    protected static ?string $model = Shipping::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    protected static string|\UnitEnum|null $navigationGroup = 'Payment';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Giao hàng';

    protected static ?string $modelLabel = 'giao hàng';

    protected static ?string $pluralModelLabel = 'giao hàng';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return ShippingsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListShippings::route('/'),
        ];
    }
}
