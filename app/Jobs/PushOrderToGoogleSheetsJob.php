<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PushOrderToGoogleSheetsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Order $order
    ) {}

    public function handle(): void
    {
        $store = $this->order->store;
        if (! $store || ! $store->google_sheets_webhook_url) {
            return;
        }

        $url = trim($store->google_sheets_webhook_url);
        if (! str_starts_with($url, 'http://') && ! str_starts_with($url, 'https://')) {
            return;
        }

        $items = $this->order->order_details['items'] ?? [];
        $itemsFormatted = array_map(fn (array $item): array => [
            'product_id' => $item['product_id'] ?? null,
            'name' => $item['name'] ?? '',
            'price' => (float) ($item['price'] ?? 0),
            'quantity' => (int) ($item['quantity'] ?? 0),
            'line_total' => (float) ($item['line_total'] ?? 0),
        ], $items);

        $payload = [
            'store_id' => $store->id,
            'store_name' => $store->name,
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number ?? (string) $this->order->id,
            'customer_name' => trim(($this->order->customer_first_name ?? '') . ' ' . ($this->order->customer_last_name ?? '')) ?: $this->order->customer_name,
            'customer_phone' => $this->order->customer_phone,
            'address' => $this->order->address,
            'subtotal' => (float) $this->order->subtotal,
            'discount_amount' => (float) $this->order->discount_amount,
            'shipping_cost' => (float) $this->order->shipping_cost,
            'total_amount' => (float) $this->order->total_amount,
            'status' => $this->order->status instanceof \BackedEnum ? $this->order->status->value : (string) $this->order->status,
            'items' => $itemsFormatted,
            'created_at' => $this->order->created_at?->toIso8601String(),
        ];

        try {
            $response = Http::timeout(15)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($url, $payload);

            if (! $response->successful()) {
                Log::warning('Google Sheets webhook failed', [
                    'order_id' => $this->order->id,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('Google Sheets webhook error: ' . $e->getMessage(), [
                'order_id' => $this->order->id,
            ]);
            throw $e;
        }
    }
}
