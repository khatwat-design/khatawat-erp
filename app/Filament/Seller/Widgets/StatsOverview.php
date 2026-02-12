<?php

namespace App\Filament\Seller\Widgets;

use App\Models\Order;
use App\Models\Product;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 2;
    }

    protected function getStats(): array
    {
        $tenant = Filament::getTenant();

        $totalSales = 0.0;
        $newOrders = 0;
        $todayOrders = 0;
        $todaySales = 0.0;
        $activeProducts = 0;

        if ($tenant) {
            $baseQuery = Order::query()->where('store_id', $tenant->id);

            $totalSales = (float) (clone $baseQuery)->sum('total_amount');

            $newOrders = (clone $baseQuery)->whereIn('status', ['pending', 'confirmed'])->count();

            $todayOrders = (clone $baseQuery)->whereDate('created_at', today())->count();
            $todaySales = (float) (clone $baseQuery)->whereDate('created_at', today())->sum('total_amount');

            $activeProducts = Product::query()
                ->where('store_id', $tenant->id)
                ->where('status', 'active')
                ->count();
        }

        return [
            Stat::make('إجمالي المبيعات', number_format($totalSales) . ' د.ع')
                ->icon('heroicon-m-banknotes')
                ->color('success')
                ->description('كل الطلبات'),
            Stat::make('طلبات جديدة', (string) $newOrders)
                ->icon('heroicon-m-shopping-cart')
                ->color('warning')
                ->description('قيد الانتظار أو التأكيد'),
            Stat::make('طلبات اليوم', (string) $todayOrders)
                ->icon('heroicon-m-calendar-days')
                ->color('info')
                ->description(number_format($todaySales) . ' د.ع'),
            Stat::make('منتجات نشطة', (string) $activeProducts)
                ->icon('heroicon-m-cube')
                ->description('معروضة في المتجر'),
        ];
    }
}
