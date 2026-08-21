<?php

namespace App\Models;

use App\Services\InventoryService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'order_item_id',
        'payment_method',
        'payment_status',
        'export_volumn',
        'confirmed_by',
        'confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'order_item_id' => 'integer',
            'export_volumn' => 'integer',
            'confirmed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Payment $payment): void {
            if ($payment->payment_status === 'confirmed') {
                $payment->confirmed_at ??= now();
                $payment->confirmed_by ??= auth()->id();

                return;
            }

            $payment->confirmed_at = null;
            $payment->confirmed_by = null;
        });

        static::updated(function (Payment $payment): void {
            if ($payment->wasChanged('payment_status') && $payment->payment_status === 'confirmed') {
                InventoryService::deductForOrder($payment->order_id);
            }
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Orders::class, 'order_id');
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id');
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

}
