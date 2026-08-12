<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Resources\Customers\CustomerResource;
use App\Filament\Resources\Customers\Schemas\CustomerForm;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Schema;

class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tạo mới')
                ->modalHeading('Thêm khách hàng mới')
                ->schema(fn (Schema $schema): Schema => CustomerForm::configure($schema))
                ->modalWidth('7xl'),
        ];
    }
}
