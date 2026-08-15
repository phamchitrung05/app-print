<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Filament\Resources\Products\Schemas\ProductForm;
use App\Models\Product;
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
                ->using(function (array $data): Product {
                    $productSkus = $data['product_skus'] ?? [];
                    unset($data['product_skus']);

                    $firstSku = collect($productSkus)->first(fn ($skuData): bool => is_array($skuData));

                    $data['user_id'] = Filament::auth()->id();
                    $data['price'] = (float) ($firstSku['price'] ?? 0);
                    $data['stock_quantity'] = (int) ($firstSku['stock'] ?? 0);

                    $product = Product::create($data);

                    foreach ($productSkus as $skuData) {
                        if (! is_array($skuData)) {
                            continue;
                        }

                        $sku = trim((string) ($skuData['sku'] ?? ''));

                        if ($sku === '') {
                            continue;
                        }

                        $product->skus()->create([
                            'sku' => $sku,
                            'price' => (float) ($skuData['price'] ?? 0),
                            'stock' => (int) ($skuData['stock'] ?? 0),
                        ]);
                    }

                    return $product;
                })
                ->modalWidth('7xl'),
        ];
    }
}
