<?php

declare(strict_types=1);

namespace App\Observers;

use App\Jobs\PushOrderToGoogleSheetsJob;
use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    public function updated(Order $order): void
    {
        $wasZero = (float) ($order->getOriginal('total_amount') ?? 0) <= 0;
        $nowHasAmount = (float) ($order->total_amount ?? 0) > 0;
        if ($wasZero && $nowHasAmount) {
            $this->notifyTelegram($order);
            $this->pushToGoogleSheets($order);
        }
    }

    private function notifyTelegram(Order $order): void
    {
        $store = $order->store;
        if (! $store) {
            return;
        }

        $token = $store->telegram_bot_token ?? $store->integrations_config['telegram_bot_token'] ?? null;
        $chatId = $store->telegram_channel_id ?? $store->integrations_config['telegram_chat_id'] ?? $store->integrations_config['telegram_channel_id'] ?? null;

        if (! $token || ! $chatId) {
            return;
        }

        $text = $this->buildTelegramInvoice($order, $store);
        if (strlen($text) > 4000) {
            $text = substr($text, 0, 3997) . '...';
        }

        try {
            Http::timeout(10)->post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'HTML',
            ]);
        } catch (\Throwable $e) {
            Log::warning('Telegram order notification failed: ' . $e->getMessage());
        }
    }

    private function buildTelegramInvoice(Order $order, $store): string
    {
        $customerName = trim(($order->customer_first_name ?? '') . ' ' . ($order->customer_last_name ?? '')) ?: ($order->customer_name ?? '—');
        $orderNum = $order->order_number ?? "#{$order->id}";
        $address = $order->address ?? '—';
        $phone = $order->customer_phone ?? '—';
        $status = $order->status instanceof \App\Enums\OrderStatus ? $order->status->label() : (string) ($order->status ?? '—');
        $createdAt = $order->created_at?->format('Y-m-d H:i') ?? now()->format('Y-m-d H:i');

        $fmt = fn ($n) => number_format((float) $n);
        $subtotal = $fmt($order->subtotal ?? 0);
        $discount = (float) ($order->discount_amount ?? 0);
        $shipping = (float) ($order->shipping_cost ?? 0);
        $total = $fmt($order->total_amount ?? 0);
        $couponCode = $order->coupon_code ? " ({$order->coupon_code})" : '';

        $text = "━━━━━━ 📋 <b>فاتورة طلب جديد</b> ━━━━━━\n\n";
        $text .= "🏪 <b>المتجر:</b> " . $this->escapeHtml($store->name) . "\n";
        $text .= "🔖 <b>رقم الطلب:</b> {$orderNum}\n";
        $text .= "📅 <b>التاريخ:</b> {$createdAt}\n";
        $text .= "📌 <b>الحالة:</b> {$status}\n\n";

        $text .= "━━━ 👤 <b>معلومات العميل</b> ━━━\n";
        $text .= "الاسم: " . $this->escapeHtml($customerName) . "\n";
        $text .= "الهاتف: {$phone}\n";
        $text .= "العنوان: " . $this->escapeHtml($address) . "\n\n";

        $text .= "━━━ 🛍️ <b>تفاصيل المنتجات</b> ━━━\n";
        $items = $order->order_details['items'] ?? [];
        $n = 1;
        foreach ($items as $item) {
            $name = $this->escapeHtml($item['name'] ?? $item['product_name'] ?? $item['title'] ?? '—');
            $price = $fmt($item['price'] ?? 0);
            $qty = (int) ($item['quantity'] ?? 0);
            $lineTotal = $fmt($item['line_total'] ?? 0);
            $text .= "{$n}. <b>" . $name . "</b>\n";
            $text .= "   السعر: {$price} د.ع × {$qty} = {$lineTotal} د.ع\n\n";
            $n++;
        }

        $text .= "━━━ 💰 <b>ملخص الفاتورة</b> ━━━\n";
        $text .= "المجموع الفرعي: {$subtotal} د.ع\n";
        if ($discount > 0) {
            $text .= "الخصم{$couponCode}: -" . $fmt($discount) . " د.ع\n";
        }
        if ($shipping > 0) {
            $text .= "الشحن: {$fmt($shipping)} د.ع\n";
        }
        $text .= "━━━━━━━━━━━━━━━━━━━━━\n";
        $text .= "📦 <b>الإجمالي النهائي: {$total} د.ع</b>\n";
        $text .= "━━━━━━━━━━━━━━━━━━━━━";

        return $text;
    }

    private function escapeHtml(?string $s): string
    {
        return htmlspecialchars((string) ($s ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    private function pushToGoogleSheets(Order $order): void
    {
        $store = $order->store;
        if (! $store || ! $store->google_sheets_webhook_url) {
            return;
        }

        PushOrderToGoogleSheetsJob::dispatchSync($order);
    }
}
