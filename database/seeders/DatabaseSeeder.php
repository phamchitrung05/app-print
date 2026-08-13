<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\OrderItem;
use App\Models\Orders;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            OrderItem::query()->delete();
            Orders::query()->delete();
            Product::query()->delete();
            Customer::query()->delete();

            $authors = User::query()->get();

            if ($authors->isEmpty()) {
                $authors = User::factory()->count(3)->create();
            }

            $customers = Customer::factory()->count(10)->create();
            $products = collect(range(0, 9))->map(
                fn (int $index): Product => Product::factory()->create([
                    'user_id' => $authors[$index % $authors->count()]->id,
                ]),
            );

            $orders = $customers->values()->map(
                fn (Customer $customer): Orders => Orders::factory()->create([
                    'customer_id' => $customer->id,
                ]),
            );

            $orders->each(function (Orders $order, int $index) use ($products): void {
                $item = OrderItem::factory()->create([
                    'order_id' => $order->id,
                    'product_id' => $products[$index]->id,
                ]);

                $order->update([
                    'total_price' => $item->quantity * $item->total_unit_price,
                ]);
            });
        });
    }
}
