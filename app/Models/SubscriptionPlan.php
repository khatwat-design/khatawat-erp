<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    protected $fillable = ['name', 'slug', 'price', 'duration_days', 'features', 'is_active'];

    protected $casts = [
        'features' => 'array',
        'is_active' => 'boolean',
        'price' => 'decimal:2',
    ];

    public function stores(): HasMany
    {
        return $this->hasMany(Store::class, 'subscription_plan_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(StorePayment::class);
    }
}
