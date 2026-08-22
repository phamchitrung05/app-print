<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrdersResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOrders extends CreateRecord
{
    protected static string $resource = OrdersResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    /**
     * Calculate the order total before the order and its items are persisted.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $items = $this->form->getRawState()['items'] ?? [];

        $subtotal = collect($items)->sum(
            fn (array $item): float => (float) ($item['quantity'] ?? 0)
                * (float) ($item['total_unit_price'] ?? 0)
        );

        $data['discount'] = max(0, (float) ($data['discount'] ?? 0));
        $data['total_price'] = max(0, $subtotal - $data['discount']);

        return $data;
    }
}
