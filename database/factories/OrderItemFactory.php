<?php

namespace Database\Factories;

use App\Models\OrderItem;
use App\Models\Orders;
use App\Models\ProductSKU;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Orders::factory(),
            'product_sku_id' => ProductSKU::factory(),
            'quantity' => fake()->numberBetween(1, 10),
            'total_unit_price' => fake()->randomFloat(2, 10000, 1000000),
        ];
    }
}
