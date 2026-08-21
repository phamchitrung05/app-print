<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductSKU extends Model
{
    use HasFactory;

    protected $table = 'product_skus';

    protected $fillable = [
        'product_id',
        'sku',
        'price',
        'stock',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function customerStocks(): HasMany
    {
        return $this->hasMany(CustomerStock::class, 'product_sku_id');
    }
}
