<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Banner extends Model
{
    protected $fillable = ['store_id', 'image_url', 'link', 'position', 'sort_order', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
