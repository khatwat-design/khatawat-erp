<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlansSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            ['name' => 'برونزية', 'slug' => 'bronze', 'price' => 50000, 'duration_days' => 30, 'features' => ['متجر واحد', '50 منتج']],
            ['name' => 'فضية', 'slug' => 'silver', 'price' => 100000, 'duration_days' => 30, 'features' => ['متجر واحد', '200 منتج', 'دعم فني']],
            ['name' => 'ذهبية', 'slug' => 'gold', 'price' => 200000, 'duration_days' => 30, 'features' => ['متجر واحد', 'منتجات غير محدودة', 'دومين مخصص', 'دعم أولوية']],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(
                ['slug' => $plan['slug']],
                array_merge($plan, ['is_active' => true])
            );
        }
    }
}
