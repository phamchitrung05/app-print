<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected array $productSkusData = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Filament::auth()->id();
        $this->productSkusData = $data['product_skus'] ?? [];

        $firstSku = collect($this->productSkusData)->first(fn ($skuData): bool => is_array($skuData));

        $data['price'] = (float) ($firstSku['price'] ?? 0);
        $data['stock_quantity'] = (int) ($firstSku['stock'] ?? 0);

        unset($data['product_skus']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->syncProductSkus();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function syncProductSkus(): void
    {
        $this->record->skus()->delete();

        foreach ($this->productSkusData as $skuData) {
            if (! is_array($skuData)) {
                continue;
            }

            $sku = trim((string) ($skuData['sku'] ?? ''));

            if ($sku === '') {
                continue;
            }

            $this->record->skus()->create([
                'sku' => $sku,
                'price' => (float) ($skuData['price'] ?? 0),
                'stock' => (int) ($skuData['stock'] ?? 0),
            ]);
        }
    }
}
