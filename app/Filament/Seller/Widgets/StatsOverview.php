<?php

namespace App\Filament\Seller\Widgets;

use App\Models\Order;
use App\Models\Product;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $tenant = Filament::getTenant();

        $totalSales = 0;
        $newOrders = 0;
        $activeProducts = 0;

        if ($tenant) {
            $totalSales = (float) Order::query()
                ->where('store_id', $tenant->id)
                ->sum('total_amount');

            $newOrders = Order::query()
                ->where('store_id', $tenant->id)
                ->where('status', 'pending')
                ->count();

            $activeProducts = Product::query()
                ->where('store_id', $tenant->id)
                ->where('status', 'active')
                ->count();
        }

        return [
            Stat::make('إجمالي المبيعات', number_format($totalSales) . ' د.ع')
                ->icon('heroicon-m-banknotes')
                ->color('success'),
            Stat::make('طلبات جديدة', (string) $newOrders)
                ->icon('heroicon-m-shopping-cart')
                ->color('warning'),
            Stat::make('منتجات نشطة', (string) $activeProducts)
                ->icon('heroicon-m-cube'),
        ];
    }
}
