<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\CustomerStock;
use App\Models\OrderItem;
use App\Models\ProductSKU;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomerStock>
 */
class CustomerStockFactory extends Factory
{
    protected $model = CustomerStock::class;

    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'product_sku_id' => ProductSKU::factory(),
            'order_item_id' => null,
            'quantity' => fake()->numberBetween(0, 100),
            'status' => fake()->randomElement(['pending', 'in_stock', 'out_of_stock', 'reserved']),
            'note' => fake()->optional()->sentence(),
        ];
    }

    public function withOrderItem(): static
    {
        return $this->state(fn (array $attributes) => [
            'order_item_id' => OrderItem::factory(),
        ]);
    }
}
