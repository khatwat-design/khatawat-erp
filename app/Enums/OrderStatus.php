<?php

declare(strict_types=1);

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Processing = 'processing';
    case ReadyToShip = 'ready_to_ship';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Completed = 'completed'; // alias for delivered (legacy)
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'قيد الانتظار',
            self::Confirmed => 'تم التأكيد',
            self::Processing => 'قيد التحضير',
            self::ReadyToShip => 'جاهز للشحن',
            self::Shipped => 'تم الشحن',
            self::Delivered, self::Completed => 'تم التوصيل',
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
            self::Shipped => 'success',
            self::Delivered, self::Completed => 'success',
            self::Cancelled => 'danger',
        };
    }
}
