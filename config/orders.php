<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Order statuses
    |--------------------------------------------------------------------------
    |
    | Danh sách trạng thái dùng chung cho form, table, filter và các vị trí
    | hiển thị trạng thái đơn hàng trong toàn bộ ứng dụng.
    |
    */
    'statuses' => [
        'new' => 'Mới tạo',
        'processing' => 'Đang xử lý',
        'completed' => 'Hoàn thành',
        'cancelled' => 'Đã Hủy',
    ],

    /*
    |--------------------------------------------------------------------------
    | Order payment statuses (status_paid)
    |--------------------------------------------------------------------------
    |
    | Lưu trạng thái thanh toán riêng của đơn hàng, tách khỏi trạng thái xử lý đơn.
    |
    */
    'status_paid' => [
        'unpaid' => 'Chưa thanh toán',
        'paid' => 'Đã thanh toán',
    ],

    /*
    |--------------------------------------------------------------------------
    | Order payment methods
    |--------------------------------------------------------------------------
    |
    | Danh sách phương thức thanh toán được phép sử dụng cho đơn hàng.
    |
    */
    'payment_methods' => [
        'cash' => 'Tiền mặt',
        'bank_transfer' => 'Chuyển khoản',
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment statuses
    |--------------------------------------------------------------------------
    |
    | Danh sách trạng thái xác nhận của các lần thanh toán.
    |
    */
    'payment_statuses' => [
        'pending' => 'Chưa thanh toán',
        'confirmed' => 'Đã thanh toán',
    ],
];
