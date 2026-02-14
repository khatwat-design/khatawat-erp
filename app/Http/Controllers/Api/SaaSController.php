<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Throwable;

class SaaSController extends Controller
{
    public function registerMerchant(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', 'unique:users,email'],
                'password' => ['required', 'string', 'min:8'],
                'store_name' => ['required', 'string', 'max:255'],
                'store_domain' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:stores,subdomain'],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->validator->errors()->first(),
                'errors' => $e->errors(),
            ], 422);
        }

        try {
            $result = DB::transaction(function () use ($validated): array {
                $store = Store::create([
                    'name' => $validated['store_name'],
                    'slug' => $validated['store_domain'],
                    'subdomain' => $validated['store_domain'],
                    'domain' => $validated['store_domain'],
                    'status' => 'active',
                    'branding_config' => [
                        'currency' => 'IQD',
                    ],
                    'settings' => [
                        'shipping_cost' => 0,
                    ],
                ]);

                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'store_id' => $store->id,
                    'role' => 'merchant',
                ]);

                return [
                    'user_id' => $user->id,
                    'store_domain' => $store->subdomain,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Store registered successfully',
                ...$result,
            ], 201);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to register store',
            ], 500);
        }
    }
}
