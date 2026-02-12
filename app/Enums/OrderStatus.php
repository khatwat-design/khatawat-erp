<?php

declare(strict_types=1);

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Processing = 'processing';
    case ReadyToShip = 'ready_to_ship';
    case WithDelivery = 'with_delivery';   // مع المندوب
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Completed = 'completed';
    case PartialReturn = 'partial_return';  // راجع جزئي
    case FullReturn = 'full_return';        // راجع كلي
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'جديد',
            self::Confirmed => 'تم التأكيد',
            self::Processing => 'قيد التجهيز',
            self::ReadyToShip => 'جاهز للشحن',
            self::WithDelivery => 'مع المندوب',
            self::Shipped => 'واصل',
            self::Delivered, self::Completed => 'تم التوصيل',
            self::PartialReturn => 'راجع جزئي',
            self::FullReturn => 'راجع كلي',
            self::Cancelled => 'ملغي',
        };
    }

    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }
        return $options;
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Confirmed => 'info',
            self::Processing => 'primary',
            self::ReadyToShip => 'gray',
            self::WithDelivery => 'info',
            self::Shipped => 'success',
            self::Delivered, self::Completed => 'success',
            self::PartialReturn, self::FullReturn => 'warning',
            self::Cancelled => 'danger',
        };
    }
}
