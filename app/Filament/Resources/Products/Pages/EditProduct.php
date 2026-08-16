<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function afterSave(): void
    {
        $this->updateProductPriceFromFirstSku();
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function updateProductPriceFromFirstSku(): void
    {
        $firstSku = $this->record->skus()->orderBy('id')->first();

        if ($firstSku) {
            $this->record->updateQuietly([
                'price' => $firstSku->price,
                'stock_quantity' => $firstSku->stock,
            ]);
        }
    }
}
