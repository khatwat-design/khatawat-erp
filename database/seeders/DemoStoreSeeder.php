<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Database\Seeder;

class DemoStoreSeeder extends Seeder
{
    public function run(): void
    {
        $store = Store::create([
            'name' => 'Khatawat Demo',
            'slug' => 'khatawat-demo',
            'api_key' => 'khat_live_DEMO_KEY_123',
            'status' => 'active',
            'branding_config' => [
                'primary_color' => '#F97316',
                'currency' => 'USD',
                'logo_url' => 'https://placehold.co/100x100/orange/white?text=K',
            ],
            'integrations_config' => [
                'telegram_bot_token' => null,
                'telegram_chat_id' => null,
                'google_sheet_url' => null,
            ],
        ]);

        $products = [
            [
                'name' => 'Khatawat Orange Mug',
                'price' => 12.99,
                'image_url' => 'https://placehold.co/600x600/orange/white?text=Mug',
                'description' => 'A sturdy ceramic mug in Khatawat orange.',
                'status' => 'active',
            ],
            [
                'name' => 'Khatawat Gray Hoodie',
                'price' => 39.00,
                'image_url' => 'https://placehold.co/600x600/6b7280/white?text=Hoodie',
                'description' => 'Soft hoodie with a minimal Khatawat logo.',
                'status' => 'active',
            ],
            [
                'name' => 'Khatawat Notebook',
                'price' => 9.50,
                'image_url' => 'https://placehold.co/600x600/111827/white?text=Notebook',
                'description' => 'Matte notebook for daily planning.',
                'status' => 'active',
            ],
            [
                'name' => 'Khatawat Sticker Pack',
                'price' => 4.25,
                'image_url' => 'https://placehold.co/600x600/f97316/white?text=Stickers',
                'description' => 'A pack of orange and gray brand stickers.',
                'status' => 'active',
            ],
        ];

        foreach ($products as $product) {
            Product::create($product + ['store_id' => $store->id]);
        }
    }
}
