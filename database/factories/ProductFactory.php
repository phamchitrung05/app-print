<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => null,
            'uuid' => (string) Str::uuid(),
            'name' => fake('vi_VN')->words(3, true),
            'product_type' => fake()->randomElement(['in_ly', 'dong_goi']),
            'unit' => fake('vi_VN')->randomElement(['Cái', 'Hộp', 'Kg', 'Chai', 'Gói']),
            'is_active' => fake()->boolean(90),
            'option' => [
                'brand' => fake()->company(),
                'origin' => fake('vi_VN')->country(),
            ],
            'internal_note' => fake('vi_VN')->optional()->sentence(),
        ];
    }
}
