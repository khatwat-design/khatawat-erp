<?php

namespace App\Filament\Seller\Resources\Coupons\Pages;

use App\Filament\Seller\Resources\Coupons\CouponResource;
use Filament\Resources\Pages\ListRecords;

class ListCoupons extends ListRecords
{
    protected static string $resource = CouponResource::class;
}
