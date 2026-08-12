<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Filament\Resources\Products\Schemas\ProductForm;
use Filament\Actions\CreateAction;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Schema;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tạo mới')
                ->modalHeading('Tạo sản phẩm mới')
                ->schema(fn (Schema $schema): Schema => ProductForm::configure($schema))
                ->mutateDataUsing(function (array $data): array {
                    $data['user_id'] = Filament::auth()->id();

                    return $data;
                })
                ->modalWidth('7xl'),
        ];
    }
}
