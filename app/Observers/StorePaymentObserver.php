<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\StorePayment;
use Carbon\Carbon;

class StorePaymentObserver
{
    public function updated(StorePayment $payment): void
    {
        if ($payment->wasChanged('status') && $payment->status === 'approved') {
            $store = $payment->store;
            if (! $store) {
                return;
            }

            $plan = $payment->plan;
            $durationDays = $plan?->duration_days ?? 30;

            $startDate = $store->subscription_expires_at && $store->subscription_expires_at->isFuture()
                ? $store->subscription_expires_at
                : Carbon::now();

            $updates = [
                'subscription_plan_id' => $payment->subscription_plan_id,
                'subscription_expires_at' => $startDate->copy()->addDays($durationDays),
                'is_active' => true,
            ];

            $store->update($updates);

            if (! $payment->paid_at) {
                $payment->updateQuietly([
                    'paid_at' => Carbon::now(),
                    'approved_by_user_id' => auth()->id(),
                ]);
            }
        }
    }
}
