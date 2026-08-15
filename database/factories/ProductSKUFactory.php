<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductSKU;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductSKU>
 */
class ProductSKUFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'sku' => fake()->unique()->bothify('SKU-####-????'),
            'price' => fake()->randomFloat(2, 10000, 1000000),
            'stock' => fake()->numberBetween(0, 500),
        ];
    }
}
