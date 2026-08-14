<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>In {{ $orders->count() }} đơn hàng - Malibu Print</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 0;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            background: #e5e7eb;
        }

        .bulk-toolbar {
            position: fixed;
            z-index: 100;
            top: 20px;
            right: 20px;
        }

        .bulk-print-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            border: 0;
            border-radius: 8px;
            background: #0879d1;
            color: #fff;
            font: 700 14px Arial, sans-serif;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0, 0, 0, .2);
        }

        .bulk-print-button svg {
            width: 18px;
            height: 18px;
        }

        .bulk-order {
            break-after: page;
        }

        .bulk-order .print-button {
            display: none !important;
        }

        .bulk-order:last-child {
            break-after: auto;
        }

        @media print {
            .bulk-toolbar {
                display: none !important;
            }

            .bulk-order {
                break-after: page;
            }

            .bulk-order:last-child {
                break-after: auto;
            }
        }
    </style>
</head>
<body>
    <div class="bulk-toolbar">
        <button type="button" class="bulk-print-button" onclick="window.print()" aria-label="In các đơn hàng">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M6 9V2h12v7"/>
                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                <path d="M6 14h12v8H6z"/>
            </svg>
            <span>In {{ $orders->count() }} đơn hàng</span>
        </button>
    </div>

    @foreach ($orders as $order)
        <div class="bulk-order">
            @include('orders.print', ['order' => $order])
        </div>
    @endforeach
</body>
</html>
