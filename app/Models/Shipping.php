<?php

namespace App\Models;

use App\Services\InventoryService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shipping extends Model
{
    protected $fillable = [
        'order_id',
        'shipping_status',
        'export_volume',
    ];

    protected $casts = [
        'export_volume' => 'integer',
    ];

    protected static function booted(): void
    {
        static::updated(function (Shipping $shipping): void {
            if ($shipping->wasChanged('shipping_status') && $shipping->shipping_status === 'delivered') {
                InventoryService::deductForOrder($shipping->order_id);
            }
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Orders::class, 'order_id');
    }
}
