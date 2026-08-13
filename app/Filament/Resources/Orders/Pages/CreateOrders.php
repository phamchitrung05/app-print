<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrdersResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOrders extends CreateRecord
{

    protected static string $resource = OrdersResource::class;

    /**
     * Calculate the order total before the order and its items are persisted.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $items = $this->form->getRawState()['items'] ?? [];

        $data['total_price'] = collect($items)->sum(
            fn (array $item): float => (float) ($item['quantity'] ?? 0)
                * (float) ($item['total_unit_price'] ?? 0)
        );

        $data['discount'] = max(0, (float) ($data['discount'] ?? 0));

        return $data;
    }
}
