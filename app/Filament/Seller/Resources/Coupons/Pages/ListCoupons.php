<?php

namespace App\Filament\Seller\Resources\Coupons\Pages;

use App\Filament\Seller\Resources\Coupons\CouponResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCoupons extends ListRecords
{
    protected static string $resource = CouponResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('إضافة كوبون')
                ->icon('heroicon-o-plus'),
        ];
    }
}
