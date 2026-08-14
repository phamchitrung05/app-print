<?php

namespace App\Models;

use Database\Factories\OrdersFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Orders extends Model
{
    /** @use HasFactory<OrdersFactory> */
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'uuid',
        'ordered_at',
        'status',
        'payment_method',
        'total_price',
        'discount',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'ordered_at' => 'datetime',
            'total_price' => 'decimal:2',
            'discount' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::created(function (Orders $order): void {
            Customer::query()
                ->whereKey($order->customer_id)
                ->update(['last_order' => $order->ordered_at]);
        });

        static::updated(function (Orders $order): void {
            $customerIds = collect([
                $order->customer_id,
                $order->getOriginal('customer_id'),
            ])->filter()->unique();

            foreach ($customerIds as $customerId) {
                self::syncCustomerLastOrder((int) $customerId);
            }

            if (
                $order->wasChanged('status')
                && $order->status === 'completed'
                && $order->getOriginal('status') !== 'completed'
            ) {
                $order->payments()->firstOrCreate([], [
                    'payment_status' => 'pending',
                ]);
            }
        });

        static::deleted(function (Orders $order): void {
            self::syncCustomerLastOrder((int) $order->customer_id);
        });
    }

    private static function syncCustomerLastOrder(int $customerId): void
    {
        $lastOrder = static::query()
            ->where('customer_id', $customerId)
            ->max('ordered_at');

        Customer::query()
            ->whereKey($customerId)
            ->update(['last_order' => $lastOrder]);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'order_id');
    }
}
