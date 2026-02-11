<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class StorefrontController extends Controller
{
    public function getSettings(Request $request): JsonResponse
    {
        $store = app()->bound('currentStore')
            ? app('currentStore')
            : null;
        if (! $store) {
            return response()->json(['message' => 'Store not found'], 404);
        }

        $branding = is_array($store->branding_config) ? $store->branding_config : [];
        $theme = is_array($store->theme_config) ? $store->theme_config : [];

        $logoPath = $theme['store_logo'] ?? $store->logo_url ?? ($branding['logo_url'] ?? null);
        $logoUrl = $this->toPublicUrl($logoPath);
        $logoUrl = $logoUrl ? $logoUrl . '?v=' . time() : null;

        return response()->json([
            'name' => $store->name,
            'logo_url' => $logoUrl,
            'currency' => $branding['currency'] ?? 'IQD',
            'shipping_cost' => $store->settings['shipping_cost'] ?? null,
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function getProducts(Request $request): JsonResponse
    {
        $store = app()->bound('currentStore')
            ? app('currentStore')
            : null;
        if (! $store) {
            return response()->json(['message' => 'Store not found'], 404);
        }

        $products = Product::query()
            ->where('store_id', $store->id)
            ->where('status', 'active')
            ->get([
                'id',
                'name',
                'price',
                'description',
                'stock',
                'image_url',
                'gallery',
            ])
            ->map(function (Product $product): array {
                $images = [];

                if ($product->image_url) {
                    $images[] = $this->toPublicUrl($product->image_url);
                }

                if (is_array($product->gallery)) {
                    foreach ($product->gallery as $path) {
                        $images[] = $this->toPublicUrl($path);
                    }
                }

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'description' => $product->description,
                    'stock' => $product->stock,
                    'images' => $images,
                ];
            });

        return response()->json($products)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function createOrder(Request $request): JsonResponse
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

            return response()->json([
                'success' => false,
                'message' => $e->validator->errors()->first(),
                'errors' => $e->errors(),
            ], 422);
        }

        $store = app()->bound('currentStore')
            ? app('currentStore')
            : null;
        if (! $store) {
            return response()->json(['message' => 'Store not found'], 404);
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

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully!',
                'order_id' => $order->id,
            ], 201);
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Order Creation Error: ' . $e->getMessage());

            return response()->json([
                'message' => 'Internal Server Error',
            ], 500);
        }
    }

    private function toPublicUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        $normalized = ltrim($path, '/');
        if (Str::startsWith($normalized, 'storage/')) {
            $normalized = Str::after($normalized, 'storage/');
        }

        return Storage::url($normalized);
    }
}
