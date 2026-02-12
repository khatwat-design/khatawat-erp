<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\StorePayment;
use App\Observers\OrderObserver;
use App\Observers\StorePaymentObserver;
use Filament\Facades\Filament;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Order::observe(OrderObserver::class);
        StorePayment::observe(StorePaymentObserver::class);

        Filament::serving(function (): void {
            app()->setLocale('ar');
        });
    }
}
