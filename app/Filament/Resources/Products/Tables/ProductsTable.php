<?php

namespace App\Filament\Resources\Products\Tables;

use App\Filament\Resources\Products\Schemas\ProductForm;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use App\Models\Product;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('skus'))
            ->columns([
                TextColumn::make('name')
                    ->label('Tên sản phẩm')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('product_type')
                    ->label('Loại sản phẩm')
                    ->formatStateUsing(
                        fn (?string $state): ?string => [
                            'in_ly' => 'In ly',
                            'in_giay' => 'In giấy',
                        ][$state] ?? $state
                    )
                    ->badge()
                    ->sortable(),

                TextColumn::make('uuid')
                    ->label('UUID')
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('unit')
                    ->label('Đơn vị tính')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('skus')
                    ->label('Giá')
                    ->getStateUsing(fn (Product $record): array => $record->skus
                        ->map(fn ($sku): HtmlString => new HtmlString(sprintf(
                            '<span class="%s">%s - %s</span>',
                            (int) $sku->stock === 0
                                ? 'text-danger-600 font-bold'
                                : 'text-info-600 font-bold',
                            e($sku->sku),
                            number_format((float) $sku->price, 0, ',', '.') . ' ₫',
                        )))
                        ->all())
                    ->listWithLineBreaks()
                    ->html()
                    ->wrap()
                    ->searchable(query: function ($query, string $search): void {
                        $query->whereHas('skus', function ($skuQuery) use ($search): void {
                            $skuQuery->where('sku', 'like', "%{$search}%");
                        });
                    }),

                IconColumn::make('is_active')
                    ->label('Hoạt động')
                    ->boolean(),

                TextColumn::make('author.name')
                    ->label('Tác giả')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Không có tác giả'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Trạng thái')
                    ->placeholder('Tất cả sản phẩm')
                    ->trueLabel('Đang hoạt động')
                    ->falseLabel('Đã ngừng hoạt động'),
            ])
            ->recordActions([
                EditAction::make()
                    ->iconButton()
                    ->modalHeading(fn ($record): string => "Chỉnh sửa sản phẩm: {$record->name}")
                    ->fillForm(fn (Product $record): array => [
                        ...$record->only([
                            'name',
                            'product_type',
                            'uuid',
                            'unit',
                            'is_active',
                            'internal_note',
                        ]),
                        'product_skus' => $record->skus()
                            ->get()
                            ->map(fn ($sku): array => [
                                'id' => $sku->id,
                                'sku' => $sku->sku,
                                'price' => $sku->price,
                                'stock' => $sku->stock,
                            ])
                            ->all(),
                    ])
                    ->schema(fn (Schema $schema): Schema => ProductForm::configure($schema))
                    ->using(function (Product $record, array $data): Product {
                        $productSkus = $data['product_skus'] ?? [];
                        unset($data['product_skus']);

                        $record->update($data);

                        $submittedIds = [];

                        foreach ($productSkus as $skuData) {
                            if (! is_array($skuData)) {
                                continue;
                            }

                            $sku = trim((string) ($skuData['sku'] ?? ''));

                            if ($sku === '') {
                                continue;
                            }

                            $skuId = $skuData['id'] ?? null;

                            $attributes = [
                                'sku' => $sku,
                                'price' => (float) ($skuData['price'] ?? 0),
                                'stock' => (int) ($skuData['stock'] ?? 0),
                            ];

                            if ($skuId) {
                                $existingSku = $record->skus()->find($skuId);

                                if ($existingSku) {
                                    $existingSku->update($attributes);
                                    $submittedIds[] = $existingSku->id;
                                } else {
                                    $newSku = $record->skus()->create($attributes);
                                    $submittedIds[] = $newSku->id;
                                }
                            } else {
                                $newSku = $record->skus()->create($attributes);
                                $submittedIds[] = $newSku->id;
                            }
                        }

                        // Delete SKUs that are no longer in the form data
                        $record->skus()->whereNotIn('id', $submittedIds)->delete();

                        return $record;
                    })
                    ->modalWidth('7xl'),

                DeleteAction::make()
                    ->iconButton(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
