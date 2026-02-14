<?php

namespace App\Filament\Seller\Resources\ShippingSettings\Pages;

use App\Filament\Seller\Resources\ShippingSettings\ShippingSettingResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateShippingSetting extends CreateRecord
{
    protected static string $resource = ShippingSettingResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $tenant = Filament::getTenant();
        if ($tenant) {
            $data['tenant_id'] = $tenant->id;
        }

        return $data;
    }
}
