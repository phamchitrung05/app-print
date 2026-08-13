<?php

use App\Models\Orders;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/orders/{order}/print', function (Orders $order) {
    $order->load(['customer', 'items.product']);

    return view('orders.print', compact('order'));
})->name('orders.print');
