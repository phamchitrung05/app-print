<?php

namespace App\Models;

use App\Services\InventoryService;
use App\Services\ReadyMadeOrderCompletionService;
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
            $shouldHandleConfirmed = $payment->wasChanged('payment_status') && $payment->payment_status === 'confirmed';

            if (! $shouldHandleConfirmed) {
                return;
            }

            try {
                InventoryService::deductForOrder($payment->order_id);
            } catch (\Throwable $e) {
                report($e);
            }

            try {
                $order = Orders::find($payment->order_id);

                if ($order === null) {
                    return;
                }

                // ready_made_goods = false: cập nhật ngay status_paid = paid khi có payment confirmed
                if (! $order->ready_made_goods) {
                    if ($order->status_paid !== 'paid') {
                        $order->forceFill(['status_paid' => 'paid'])->saveQuietly();
                    }

                    return;
                }

                // ready_made_goods = true: không cập nhật trực tiếp, ủy quyền cho ReadyMadeOrderCompletionService
                // Service sẽ kiểm tra 2 điều kiện (tồn = 0 + tất cả payment per-SKU confirmed) rồi mới set paid
                ReadyMadeOrderCompletionService::tryCompleteOrder($payment->order_id);
            } catch (\Throwable $e) {
                report($e);
            }
        });

        // Khi tạo payment đã ở trạng thái confirmed ngay từ đầu
        static::created(function (Payment $payment): void {
            if ($payment->payment_status !== 'confirmed') {
                return;
            }

            try {
                $order = Orders::find($payment->order_id);

                if ($order === null) {
                    return;
                }

                if (! $order->ready_made_goods) {
                    if ($order->status_paid !== 'paid') {
                        $order->forceFill(['status_paid' => 'paid'])->saveQuietly();
                    }

                    return;
                }

                ReadyMadeOrderCompletionService::tryCompleteOrder($payment->order_id);
            } catch (\Throwable $e) {
                report($e);
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
