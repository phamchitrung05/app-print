<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrdersResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOrders extends EditRecord
{
    protected static string $resource = OrdersResource::class;

    /**
     * Recalculate the saved order total from all item line totals before updating.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
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

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
