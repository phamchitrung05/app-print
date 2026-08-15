<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\OrderItem;
use App\Models\Orders;
use App\Models\Product;
use App\Models\ProductSKU;
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
            ProductSKU::query()->delete();
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

            $productSkus = $products->values()->map(
                fn (Product $product, int $index): ProductSKU => ProductSKU::factory()->create([
                    'product_id' => $product->id,
                    'sku' => sprintf('SKU-%05d', $index + 1),
                ]),
            );

            $orders = $customers->values()->map(
                fn (Customer $customer): Orders => Orders::factory()->create([
                    'customer_id' => $customer->id,
                ]),
            );

            $orders->each(function (Orders $order, int $index) use ($productSkus): void {
                $productSku = $productSkus[$index];
                $quantity = fake()->numberBetween(1, 10);

                OrderItem::factory()->create([
                    'order_id' => $order->id,
                    'product_sku_id' => $productSku->id,
                    'quantity' => $quantity,
                    'total_unit_price' => $productSku->price,
                ]);

                $order->update([
                    'total_price' => ($quantity * (float) $productSku->price) - (float) $order->discount,
                ]);
            });
        });
    }
}
