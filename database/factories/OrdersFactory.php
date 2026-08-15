<?php

namespace Database\Factories;

use App\Models\Customer;
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
            'customer_id' => Customer::factory(),
            'uuid' => (string) Str::uuid(),
            'ordered_at' => fake()->dateTimeBetween('-6 months', 'now'),
            'status' => fake()->randomElement(['new', 'processing', 'completed', 'cancelled']),
            'total_price' => 0,
            'discount' => fake()->randomFloat(2, 0, 100000),
            'note' => fake('vi_VN')->optional()->sentence(),
        ];
    }
}
