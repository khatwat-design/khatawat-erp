<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    public function created(Order $order): void
    {
        $this->notifyTelegram($order);
    }

    private function notifyTelegram(Order $order): void
    {
        $store = $order->store;
        if (! $store) {
            return;
        }

        $token = $store->telegram_bot_token ?? $store->integrations_config['telegram_bot_token'] ?? null;
        $chatId = $store->telegram_channel_id ?? $store->integrations_config['telegram_chat_id'] ?? null;

        if (! $token || ! $chatId) {
            return;
        }

        $customerName = trim(($order->customer_first_name ?? '') . ' ' . ($order->customer_last_name ?? '')) ?: $order->customer_name;
        $total = number_format((float) $order->total_amount);

        $text = "🛒 *طلب جديد*\n\n";
        $text .= "المتجر: {$store->name}\n";
        $text .= "رقم الطلب: " . ($order->order_number ?? "#{$order->id}") . "\n";
        $text .= "العميل: {$customerName}\n";
        $text .= "الهاتف: {$order->customer_phone}\n";
        $text .= "الإجمالي: {$total} د.ع\n";

        try {
            Http::timeout(5)->post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'Markdown',
            ]);
        } catch (\Throwable $e) {
            Log::warning('Telegram order notification failed: ' . $e->getMessage());
        }
    }
}
