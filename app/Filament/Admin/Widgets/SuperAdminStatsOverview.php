<?php

declare(strict_types=1);

namespace App\Filament\Admin\Widgets;

use App\Models\Order;
use App\Models\Store;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SuperAdminStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {

        $totalStores = Store::query()->count();
        $totalOrders = Order::query()->count();
        $totalRevenue = (float) Order::query()->sum('total_amount');
        $todayOrders = Order::query()->whereDate('created_at', today())->count();

        return [
            Stat::make('إجمالي المتاجر', (string) $totalStores)
                ->icon('heroicon-m-building-storefront')
                ->description('متاجر مسجلة'),
            Stat::make('إجمالي الطلبات', (string) $totalOrders)
                ->icon('heroicon-m-shopping-cart')
                ->description($todayOrders . ' طلب اليوم'),
            Stat::make('إجمالي الإيرادات', number_format($totalRevenue) . ' د.ع')
                ->icon('heroicon-m-banknotes')
                ->color('success'),
            Stat::make('المتاجر النشطة', (string) Store::query()->where('is_active', true)->count())
                ->icon('heroicon-m-check-circle')
                ->color('success'),
        ];
    }
}
