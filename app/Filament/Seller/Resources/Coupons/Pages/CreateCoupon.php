<?php

namespace App\Filament\Seller\Resources\Coupons\Pages;

use App\Filament\Seller\Resources\Coupons\CouponResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateCoupon extends CreateRecord
{
    protected static string $resource = CouponResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $tenant = Filament::getTenant();
        if ($tenant) {
            $data['store_id'] = $tenant->id;
        }
        return $data;
    }
}
