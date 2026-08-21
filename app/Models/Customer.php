<?php

namespace App\Models;

use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Customer extends Model
{
    /** @use HasFactory<CustomerFactory> */
    use HasFactory;

    protected $fillable = [
        'uuid',
        'name',
        'phone',
        'address',
        'note',
        'is_active',
        'option',
        'last_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'option' => 'array',
            'last_order' => 'datetime',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Orders::class);
    }

    public function customerStocks(): HasMany
    {
        return $this->hasMany(CustomerStock::class);
    }
}
