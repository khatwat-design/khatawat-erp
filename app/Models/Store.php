<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use App\Models\WalletTransaction;
use App\Models\ShippingSetting;

class Store extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'subdomain',
        'custom_domain',
        'wallet_balance',
        'theme_id',
        'theme_config',
        'modules_status',
        'settings',
        'api_key',
        'domain',
        'status',
        'branding_config',
        'integrations_config',
        'telegram_bot_token',
        'telegram_channel_id',
        'google_sheets_token',
        'logo_url',
        'subscription_plan',
        'subscription_expires_at',
        'is_active',
        'manager_phone',
        'facebook_pixel_id',
        'tiktok_pixel_id',
        'snapchat_pixel_id',
        'google_analytics_id',
    ];

    protected $casts = [
        'branding_config' => 'array',
        'integrations_config' => 'array',
        'theme_config' => 'array',
        'modules_status' => 'array',
        'settings' => 'array',
        'subscription_expires_at' => 'date',
        'is_active' => 'boolean',
        'wallet_balance' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $store): void {
            if ($store->api_key === null || $store->api_key === '') {
                $store->api_key = Str::random(40);
            }

            if ($store->subdomain === null || $store->subdomain === '') {
                $base = Str::slug($store->name) ?: 'store';
                do {
                    $candidate = $base . '-' . Str::lower(Str::random(4));
                } while (self::query()->where('subdomain', $candidate)->exists());
                $store->subdomain = $candidate;
            }
        });
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function walletTransactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class, 'tenant_id');
    }

    public function shippingSettings(): HasMany
    {
        return $this->hasMany(ShippingSetting::class, 'tenant_id');
    }
}
