<?php

declare(strict_types=1);

namespace App\Filament\Admin\Widgets;

use App\Models\Order;
use App\Models\Store;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SuperAdminStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 3;
    }

    protected function getStats(): array
    {
        $totalStores = Store::query()->count();
        $newStoresThisMonth = Store::query()->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $totalOrders = Order::query()->count();
        $totalRevenue = (float) Order::query()->sum('total_amount');
        $todayOrders = Order::query()->whereDate('created_at', today())->count();
        $monthlyRevenue = (float) Order::query()->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->sum('total_amount');
        $activeStores = Store::query()->where('is_active', true)->count();
        $inactiveStores = Store::query()->where('is_active', false)->count();
        $expiringSoon = Store::query()->whereNotNull('subscription_expires_at')
            ->whereBetween('subscription_expires_at', [now(), now()->addDays(7)])
            ->count();

        $diskTotal = @disk_total_space(storage_path()) ?: 0;
        $diskFree = @disk_free_space(storage_path()) ?: 0;
        $diskUsedPercent = $diskTotal > 0 ? round((($diskTotal - $diskFree) / $diskTotal) * 100) : 0;

        return [
            Stat::make('إجمالي المتاجر', (string) $totalStores)
                ->icon('heroicon-m-building-storefront')
                ->description($newStoresThisMonth . ' متجر جديد هذا الشهر'),
            Stat::make('إجمالي الطلبات', (string) $totalOrders)
                ->icon('heroicon-m-shopping-cart')
                ->description($todayOrders . ' طلب اليوم'),
            Stat::make('إجمالي الإيرادات', number_format($totalRevenue) . ' د.ع')
                ->icon('heroicon-m-banknotes')
                ->color('success')
                ->description('شهري: ' . number_format($monthlyRevenue) . ' د.ع'),
            Stat::make('المتاجر النشطة', (string) $activeStores)
                ->icon('heroicon-m-check-circle')
                ->color('success')
                ->description($inactiveStores . ' متوقف'),
            Stat::make('اشتراكات قريبة الانتهاء', (string) $expiringSoon)
                ->icon('heroicon-m-clock')
                ->color($expiringSoon > 0 ? 'warning' : 'gray')
                ->description('خلال 7 أيام'),
            Stat::make('مساحة التخزين', $diskUsedPercent . '% مستخدم')
                ->icon('heroicon-m-server-stack')
                ->color($diskUsedPercent > 90 ? 'danger' : 'gray')
                ->description(round($diskTotal / 1024 / 1024 / 1024, 1) . ' جيجا إجمالي'),
        ];
    }
}
