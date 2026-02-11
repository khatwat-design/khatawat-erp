<?php

declare(strict_types=1);

namespace App\Filament\Admin\Widgets;

use App\Models\Store;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class SuperAdminStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $now = Carbon::now();
        $expiringSoon = $now->copy()->addDays(7)->toDateString();

        return [
            Stat::make('إجمالي العملاء', Store::query()->count()),
            Stat::make('اشتراكات نشطة', Store::query()->where('is_active', true)->count()),
            Stat::make('تنتهي قريبًا', Store::query()
                ->whereNotNull('subscription_expires_at')
                ->whereDate('subscription_expires_at', '<=', $expiringSoon)
                ->whereDate('subscription_expires_at', '>=', $now->toDateString())
                ->count()),
        ];
    }
}
