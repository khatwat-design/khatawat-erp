<?php

namespace App\Filament\Seller\Resources\Banners\Pages;

use App\Filament\Seller\Resources\Banners\BannerResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateBanner extends CreateRecord
{
    protected static string $resource = BannerResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $tenant = Filament::getTenant();
        if ($tenant) {
            $data['store_id'] = $tenant->id;
        }
        return $data;
    }
}
