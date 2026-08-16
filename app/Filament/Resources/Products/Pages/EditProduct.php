<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected array $productSkusData = [];

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['product_skus'] = $this->record->skus()
            ->get()
            ->map(function ($sku): array {
                return [
                    'sku' => $sku->sku,
                    'price' => $sku->price,
                    'stock' => $sku->stock,
                ];
            })
            ->all();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->productSkusData = $data['product_skus'] ?? [];

        $firstSku = collect($this->productSkusData)->first(fn ($skuData): bool => is_array($skuData));

        $data['price'] = (float) ($firstSku['price'] ?? 0);
        $data['stock_quantity'] = (int) ($firstSku['stock'] ?? 0);

        unset($data['product_skus']);

        return $data;
    }

    protected function afterSave(): void
    {
        $this->syncProductSkus();
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

    protected function syncProductSkus(): void
    {
        DB::transaction(function () {
            $existingSkuIds = [];

            foreach ($this->productSkusData as $skuData) {
                if (! is_array($skuData)) {
                    continue;
                }

                $sku = trim((string) ($skuData['sku'] ?? ''));

                if ($sku === '') {
                    continue;
                }

                $skuId = $skuData['id'] ?? null;

                if ($skuId) {
                    $skuModel = $this->record->skus()
                        ->whereKey($skuId)
                        ->first();

                    if (! $skuModel) {
                        continue;
                    }

                    $skuModel->update([
                        'sku' => $sku,
                        'price' => (float) ($skuData['price'] ?? 0),
                        'stock' => (int) ($skuData['stock'] ?? 0),
                    ]);

                    $existingSkuIds[] = $skuModel->id;
                } else {
                    $skuModel = $this->record->skus()->create([
                        'sku' => $sku,
                        'price' => (float) ($skuData['price'] ?? 0),
                        'stock' => (int) ($skuData['stock'] ?? 0),
                    ]);

                    $existingSkuIds[] = $skuModel->id;
                }
            }

            // Xóa những SKU đã bị người dùng remove khỏi form
            $this->record->skus()
                ->whereNotIn('id', $existingSkuIds)
                ->delete();
        });
    }
}
