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
        'note',
    ];

    protected function casts(): array
    {
        return [
            'ordered_at' => 'datetime',
            'total_price' => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

}
