<?php

namespace Database\Factories;

use App\Models\Orders;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Orders>
 */
class OrdersFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => null,
            'uuid' => (string) Str::uuid(),
            'ordered_at' => fake()->dateTimeBetween('-6 months', 'now'),
            'status' => fake()->randomElement(['new', 'processing', 'completed', 'cancelled']),
            'payment_method' => fake()->randomElement(['cash', 'bank_transfer', 'card']),
            'total_price' => 0,
            'note' => fake('vi_VN')->optional()->sentence(),
        ];
    }
}
