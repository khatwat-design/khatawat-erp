<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MerchantUserSeeder extends Seeder
{
    public function run(): void
    {
        $store = Store::query()->first();

        if (! $store) {
            $store = Store::create([
                'name' => 'Default Store',
                'slug' => 'default-store',
                'status' => 'active',
                'branding_config' => [
                    'primary_color' => '#F97316',
                    'currency' => 'USD',
                ],
                'integrations_config' => [],
            ]);
        }

        User::query()->updateOrCreate(
            ['email' => 'merchant@khatawat.com'],
            [
                'name' => 'Merchant',
                'password' => Hash::make('password'),
                'store_id' => $store->id,
                'role' => 'owner',
            ]
        );
    }
}
