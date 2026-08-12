<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'name' => fake('vi_VN')->name(),
            'phone' => '0' . fake()->numerify('#########'),
            'address' => fake('vi_VN')->address(),
            'note' => fake('vi_VN')->optional()->sentence(),
            'is_active' => fake()->boolean(85),
            'option' => [
                'nguon' => fake('vi_VN')->randomElement(['Giới thiệu', 'Facebook', 'Website', 'Khác']),
                'nhom' => fake('vi_VN')->randomElement(['Cá nhân', 'Doanh nghiệp', 'Thân thiết']),
            ],
        ];
    }
}
