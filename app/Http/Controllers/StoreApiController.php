<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StoreApiController extends Controller
{
    private function withDebugHeader(JsonResponse $response): JsonResponse
    {
        return $response->header('X-Debug-App-Url', config('app.url'));
    }

    private function getStore(Request $request): Store | JsonResponse
    {
        // Multi-tenant: prefer store resolved by IdentifyTenant (X-Store-Domain / ?domain=)
        if (app()->bound('currentStore')) {
            return app('currentStore');
        }

        $apiKey = $request->header('X-Store-API-Key')
            ?? $request->header('X-API-KEY');

        if (! $apiKey) {
            return $this->withDebugHeader(
                response()->json(['message' => 'Store not found. Send ?domain= or X-Store-Domain for the store.'], 404)
            );
        }

        $store = Store::query()
            ->where('api_key', $apiKey)
            ->first();

        if (! $store) {
            return $this->withDebugHeader(
                response()->json(['message' => 'Unauthorized'], 401)
            );
        }

        return $store;
    }

    public function index(Request $request): JsonResponse
    {
        $store = $this->getStore($request);
        if ($store instanceof JsonResponse) {
            return $store;
        }

        $branding = $store->branding_config ?? [];
        $logoPath = $store->logo_url ?: ($branding['logo_url'] ?? null);
        $logoUrl = $logoPath
            ? (str_starts_with($logoPath, 'http://') || str_starts_with($logoPath, 'https://')
                ? $logoPath
                : asset('storage/' . ltrim($logoPath, '/')))
            : null;

        $settings = is_array($store->settings) ? $store->settings : [];

        return $this->withDebugHeader(response()->json([
            'name' => $store->name,
            'store_name' => $store->name,
            'logo_url' => $logoUrl,
            'brand_color' => $branding['primary_color'] ?? $branding['brand_color'] ?? '#F97316',
            'currency' => $branding['currency'] ?? 'IQD',
            'shipping_cost' => $settings['shipping_cost'] ?? 0,
            'facebook_pixel_id' => $store->facebook_pixel_id,
            'tiktok_pixel_id' => $store->tiktok_pixel_id,
            'snapchat_pixel_id' => $store->snapchat_pixel_id,
            'google_analytics_id' => $store->google_analytics_id,
        ]));
    }

    public function products(Request $request): JsonResponse
    {
        $store = $this->getStore($request);
        if ($store instanceof JsonResponse) {
            return $store;
        }

        $products = $store->products()
            ->get([
                'id',
                'name',
                'price',
                'image_url',
                'gallery',
                'description',
                'status',
            ])
            ->map(function ($product): array {
                $imageUrl = $product->image_url
                    ? asset('storage/' . $product->image_url)
                    : null;
                $gallery = $product->gallery
                    ? array_map(
                        fn (string $path): string => asset('storage/' . $path),
                        $product->gallery
                    )
                    : [];

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'image_url' => $imageUrl,
                    'gallery' => $gallery,
                    'description' => $product->description,
                    'status' => $product->status,
                ];
            });

        return $this->withDebugHeader(response()->json($products));
    }

    public function banners(Request $request): JsonResponse
    {
        $store = $this->getStore($request);
        if ($store instanceof JsonResponse) {
            return $store;
        }

        $banners = $store->banners()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'image_url', 'link', 'position', 'sort_order'])
            ->map(function ($banner): array {
                $imageUrl = $banner->image_url
                    ? (str_starts_with($banner->image_url, 'http')
                        ? $banner->image_url
                        : asset('storage/' . ltrim($banner->image_url, '/')))
                    : null;

                return [
                    'id' => $banner->id,
                    'image_url' => $imageUrl,
                    'link' => $banner->link,
                    'position' => $banner->position,
                ];
            });

        return $this->withDebugHeader(response()->json($banners));
    }

    public function showProduct(Request $request, int $productId): JsonResponse
    {
        $store = $this->getStore($request);
        if ($store instanceof JsonResponse) {
            return $store;
        }

        $product = $store->products()
            ->whereKey($productId)
            ->first();

        if (! $product) {
            return $this->withDebugHeader(
                response()->json(['message' => 'Not found'], 404)
            );
        }

        $imageUrl = $product->image_url
            ? asset('storage/' . $product->image_url)
            : null;
        $gallery = $product->gallery
            ? array_map(
                fn (string $path): string => asset('storage/' . $path),
                $product->gallery
            )
            : [];

        return $this->withDebugHeader(response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'price' => $product->price,
            'image_url' => $imageUrl,
            'gallery' => $gallery,
        ]));
    }

    public function createOrder(Request $request): JsonResponse
    {
        $store = $this->getStore($request);
        if ($store instanceof JsonResponse) {
            return $store;
        }

        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:50'],
            'total_amount' => ['required', 'numeric', 'min:0'],
            'order_details' => ['required', 'array'],
            'status' => ['nullable', 'in:pending,shipped,cancelled'],
        ]);

        $order = Order::create([
            'store_id' => $store->id,
            'customer_name' => $validated['customer_name'],
            'customer_phone' => $validated['customer_phone'],
            'total_amount' => $validated['total_amount'],
            'order_details' => $validated['order_details'],
            'status' => $validated['status'] ?? 'pending',
        ]);

        return response()->json([
            'message' => 'Order created',
            'order_id' => $order->id,
        ], 201);
    }
}
