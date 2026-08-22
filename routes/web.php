<?php

use App\Models\Orders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/orders/print-bulk', function (Request $request) {
    $orderIds = collect(explode(',', (string) $request->query('ids', '')))
        ->filter(fn ($id): bool => is_numeric($id))
        ->map(fn ($id): int => (int) $id)
        ->unique()
        ->values();

    abort_if($orderIds->isEmpty(), 404);

    $orders = Orders::query()
        ->with(['customer', 'items.product'])
        ->whereIn('id', $orderIds)
        ->where('status', '!=', 'cancelled')
        ->get()
        ->sortBy(fn (Orders $order): int => $orderIds->search($order->id))
        ->values();

    abort_if($orders->isEmpty(), 404, 'Không thể in đơn đã hủy.');

    return view('orders.print-bulk', compact('orders'));
})->name('orders.print.bulk');

Route::get('/orders/{order}/print', function (Orders $order) {
    abort_if($order->status === 'cancelled', 404, 'Không thể in đơn đã hủy.');

    $order->load(['customer', 'items.product']);

    return view('orders.print', compact('order'));
})->name('orders.print');
