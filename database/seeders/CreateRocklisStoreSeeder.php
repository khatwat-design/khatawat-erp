<?php

namespace Database\Seeders;

use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Seeder;

class CreateRocklisStoreSeeder extends Seeder
{
    public function run(): void
    {
        $store = Store::create([
            'name' => 'روكلس',
            'slug' => 'rocklis',
            'subdomain' => 'rocklis',
            'status' => 'active',
            'branding_config' => [
                'primary_color' => '#F97316',
                'currency' => 'IQD',
                'logo_url' => null,
            ],
        ]);

        $user = User::firstOrNew(['email' => 'tajer@rocklis.com']);
        $user->name = 'تاجر روكلس';
        $user->password = bcrypt('password123');
        $user->store_id = $store->id;
        $user->role = 'owner';
        $user->save();

        $this->command->info("تم! المتجر: روكلس | البريد: tajer@rocklis.com | كلمة المرور: password123");
    }
}
