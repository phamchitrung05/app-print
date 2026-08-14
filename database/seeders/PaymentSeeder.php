<?php

namespace Database\Seeders;

use App\Models\Orders;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $orders = Orders::query()->pluck('id');

        if ($orders->isEmpty()) {
            $this->command?->error('Không có Order nào để gắn Payment.');

            return;
        }

        $userId = User::query()->value('id');

        for ($i = 1; $i <= 10; $i++) {
            $status = $i % 3 === 0 ? 'confirmed' : 'pending';

            Payment::create([
                'order_id' => $orders->random(),
                'payment_status' => $status,
                'confirmed_by' => $status === 'confirmed' ? $userId : null,
                'confirmed_at' => $status === 'confirmed' ? now()->subDays(random_int(0, 7)) : null,
            ]);
        }

        $this->command?->info('Đã tạo 10 Payment fake.');
    }
}
