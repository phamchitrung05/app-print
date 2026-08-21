<?php

namespace App\Models;

use Database\Factories\OrdersFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;
use App\Models\CustomerStock;

class Orders extends Model
{
    /** @use HasFactory<OrdersFactory> */
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'uuid',
        'code',
        'ordered_at',
        'status',
        'ready_made_goods',
        'payment_method',
        'total_price',
        'discount',
        'note',
        'inventory_deducted_at',
    ];

    protected function casts(): array
    {
        return [
            'ordered_at' => 'datetime',
            'inventory_deducted_at' => 'datetime',
            'ready_made_goods' => 'boolean',
            'total_price' => 'decimal:2',
            'discount' => 'decimal:2',
        ];
    }

    /**
     * Đăng ký các model event cho Orders.
     * - Tự sinh mã đơn, đồng bộ last_order của khách
     * - Khi đơn chuyển sang completed thì tự tạo payment/shipping/customer_stock và trừ tồn kho SKU
     */
    protected static function booted(): void
    {
        // Khi tạo mới đơn hàng: tự sinh mã code 5 chữ số và cập nhật last_order cho khách
        static::created(function (Orders $order): void {
            // Nếu chưa có mã (do fillable không có code) thì sinh mã dựa trên ID: pad 5 chữ số (vd 00012)
            // Dùng saveQuietly để không kích hoạt lại event updated gây vòng lặp
            if ($order->code === null) {
                $order->forceFill([
                    'code' => str_pad((string) $order->getKey(), 5, '0', STR_PAD_LEFT),
                ])->saveQuietly();
            }

            // Cập nhật ngày đặt gần nhất của khách hàng để hiển thị last_order
            Customer::query()
                ->whereKey($order->customer_id)
                ->update(['last_order' => $order->ordered_at]);
        });

        // Khi cập nhật đơn hàng: đồng bộ last_order và xử lý nghiệp vụ khi chuyển sang completed
        static::updated(function (Orders $order): void {
            // Lấy danh sách khách bị ảnh hưởng (khách cũ và khách mới nếu đổi customer_id) để đồng bộ lại last_order
            $customerIds = collect([
                $order->customer_id,
                $order->getOriginal('customer_id'),
            ])->filter()->unique();

            // Đồng bộ last_order cho từng khách (lấy max ordered_at còn lại)
            foreach ($customerIds as $customerId) {
                self::syncCustomerLastOrder((int) $customerId);
            }

            // Chỉ xử lý khi trạng thái vừa chuyển sang completed lần đầu (tránh chạy lại khi update field khác)
            if (
                $order->wasChanged('status')
                && $order->status === 'completed'
                && $order->getOriginal('status') !== 'completed'
            ) {
                // Phân nhánh theo loại đơn: làm sẵn vs đặt làm
                // - ready_made_goods=true (hàng làm sẵn) : KHÔNG tạo payment khi completed; payment sẽ được tạo dần khi xuất kho từng phần trong CustomerStocks
                // - ready_made_goods=false (đặt làm) : tạo payment pending để theo dõi thanh toán ngay
                if ($order->ready_made_goods) {
                    // Đơn làm sẵn (ready_made_goods = true): không tạo shipping, thay vào đó nhập vào tồn kho sẵn hàng
                    // Mỗi order_item sẽ tạo 1 dòng customer_stocks để theo dõi tồn của khách
                    // Dùng firstOrCreate theo order_item_id (cột unique) để idempotent - gọi lại không tạo trùng
                    $items = $order->relationLoaded('items') ? $order->items : $order->items()->get();

                    foreach ($items as $item) {
                        CustomerStock::firstOrCreate(
                            ['order_item_id' => $item->getKey()], // Khóa unique: 1 order_item chỉ có 1 dòng tồn
                            [
                                'customer_id' => $order->customer_id, // Gán tồn cho đúng khách của đơn
                                'product_sku_id' => $item->product_sku_id, // SKU tương ứng
                                'quantity' => $item->quantity, // Số lượng làm sẵn
                                'status' => 'in_stock', // Mặc định là còn hàng (đã hoàn thành nên có sẵn để giao)
                            ]
                        );
                    }
                } else {
                    // Đơn đặt làm (ready_made_goods = false): tạo payment + shipping pending để theo dõi
                    $order->payments()->firstOrCreate([], [
                        'payment_status' => 'pending',
                        'export_volumn' => 0,
                    ]);
                    $order->shipping()->firstOrCreate([], [
                        'shipping_status' => 'pending',
                    ]);
                }

                // Trừ tồn kho kho tổng (product_skus.stock) ngay khi hoàn thành
                // InventoryService đã xử lý idempotent qua inventory_deducted_at nên gọi nhiều lần vẫn an toàn
                // Bọc try/catch để không làm rollback cả transaction cập nhật status nếu trừ kho lỗi (chỉ report)
                try {
                    \App\Services\InventoryService::deductForOrder($order);
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        });

        // Trước khi xóa đơn: xóa kèm payment và shipping để tránh rác dữ liệu
        // (shipping hiện cho phép nhiều dòng trên 1 order_id - mỗi lần xuất kho tạo 1 record)
        static::deleting(function (Orders $order): void {
            $order->payments()->delete();
            $order->shippings()->delete();
        });

        // Sau khi xóa đơn: đồng bộ lại last_order cho khách (lấy đơn gần nhất còn lại)
        static::deleted(function (Orders $order): void {
            self::syncCustomerLastOrder((int) $order->customer_id);
        });
    }

    /**
     * Đồng bộ ngày đặt hàng gần nhất (last_order) cho một khách hàng.
     * Lấy max ordered_at từ các đơn còn lại của khách đó.
     *
     * @param int $customerId ID khách hàng cần đồng bộ
     */
    private static function syncCustomerLastOrder(int $customerId): void
    {
        // Lấy ngày đặt lớn nhất còn lại; nếu không còn đơn thì trả về null
        $lastOrder = static::query()
            ->where('customer_id', $customerId)
            ->max('ordered_at');

        // Cập nhật trực tiếp vào bảng customers (query builder để không kích hoạt model event)
        Customer::query()
            ->whereKey($customerId)
            ->update(['last_order' => $lastOrder]);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'order_id');
    }

    public function shipping(): HasOne
    {
        return $this->hasOne(Shipping::class, 'order_id');
    }

    public function shippings(): HasMany
    {
        return $this->hasMany(Shipping::class, 'order_id');
    }
}
