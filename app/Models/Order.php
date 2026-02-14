<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'order_number',
        'customer_first_name',
        'customer_last_name',
        'customer_name',
        'customer_phone',
        'address',
        'subtotal',
        'discount_amount',
        'coupon_code',
        'coupon_details',
        'shipping_cost',
        'total_amount',
        'order_details',
        'status',
        'payment_status',
        'tracking_number',
        'seller_notes',
    ];

    protected $casts = [
        'order_details' => 'array',
        'coupon_details' => 'array',
        'status' => OrderStatus::class,
    ];

    protected static function booted(): void
    {
        static::creating(function (Order $order): void {
            if (empty($order->order_number)) {
                $count = self::query()->whereDate('created_at', today())->count() + 1;
                $order->order_number = 'ORD-' . now()->format('Ymd') . '-' . str_pad((string) $count, 4, '0', STR_PAD_LEFT);
            }
        });

        static::updating(function (Order $order): void {
            if ($order->isDirty('status') && $order->id) {
                $status = $order->status;
                $statusValue = $status instanceof OrderStatus ? $status->value : (string) $status;
                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'status' => $statusValue,
                    'user_id' => auth()->id(),
                ]);
            }
        });
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->orderByDesc('created_at');
    }
}
