<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class OrderController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'customer_first_name' => ['required', 'string', 'max:255'],
                'customer_last_name' => ['required', 'string', 'max:255'],
                'phone' => ['required', 'string', 'max:50'],
                'address' => ['required', 'string', 'max:500'],
                'items' => ['required', 'array', 'min:1'],
                'items.*.product_id' => ['required', 'exists:products,id'],
                'items.*.quantity' => ['required', 'integer', 'min:1'],
            ]);
        } catch (ValidationException $e) {
            Log::error($e->getMessage());

            return $this->corsResponse([
                'success' => false,
                'message' => $e->validator->errors()->first(),
                'errors' => $e->errors(),
            ], 422);
        }

        $store = $this->resolveStoreByDomain($request);
        if (! $store) {
            return $this->corsResponse([
                'success' => false,
                'message' => 'Store not found',
            ], 404);
        }

        DB::beginTransaction();

        try {
            $order = Order::create([
                'store_id' => $store->id,
                'customer_first_name' => $validated['customer_first_name'],
                'customer_last_name' => $validated['customer_last_name'],
                'customer_name' => trim($validated['customer_first_name'] . ' ' . $validated['customer_last_name']),
                'customer_phone' => $validated['phone'],
                'address' => $validated['address'],
                'total_amount' => 0,
                'order_details' => [],
                'status' => 'pending',
                'payment_status' => 'pending',
            ]);

            $total = 0;
            $itemsSnapshot = [];

            foreach ($validated['items'] as $itemData) {
                $product = Product::query()
                    ->where('store_id', $store->id)
                    ->whereKey($itemData['product_id'])
                    ->lockForUpdate()
                    ->first();

                if (! $product) {
                    throw new \RuntimeException('Product not found');
                }

                $quantity = (int) $itemData['quantity'];
                $unitPrice = (float) $product->price;

                if ($product->stock !== null && $product->stock < $quantity) {
                    throw new \RuntimeException('Insufficient stock for: ' . $product->name);
                }

                $lineTotal = $unitPrice * $quantity;
                $total += $lineTotal;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                ]);

                if ($product->stock !== null) {
                    $product->decrement('stock', $quantity);
                }

                $itemsSnapshot[] = [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'price' => $unitPrice,
                    'quantity' => $quantity,
                    'line_total' => $lineTotal,
                ];
            }

            $order->update([
                'total_amount' => $total,
                'order_details' => ['items' => $itemsSnapshot],
            ]);

            DB::commit();

            return $this->corsResponse([
                'success' => true,
                'message' => 'Order created successfully',
                'order_id' => $order->id,
            ], 201);
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error($e->getMessage());

            return $this->corsResponse([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    private function resolveStoreByDomain(Request $request): ?Store
    {
        $domain = $request->header('X-Store-Domain')
            ?? $request->query('domain')
            ?? $request->getHost();

        if (! $domain) {
            return null;
        }

        $domain = strtolower($domain);

        if (in_array($domain, ['localhost', '127.0.0.1'], true)) {
            return Store::query()->first();
        }

        $store = Store::query()->where('custom_domain', $domain)->first();
        if ($store) {
            return $store;
        }

        $parts = explode('.', $domain);
        if (count($parts) > 2) {
            $subdomain = $parts[0];
            return Store::query()->where('subdomain', $subdomain)->first();
        }

        return Store::query()->where('domain', $domain)->first();
    }

    private function corsResponse(array $data, int $status = 200): JsonResponse
    {
        return response()
            ->json($data, $status)
            ->header('Access-Control-Allow-Origin', 'http://localhost:3000')
            ->header('Access-Control-Allow-Credentials', 'true');
    }
}
