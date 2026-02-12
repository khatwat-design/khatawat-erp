<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\StorePaymentResource\Pages;

use App\Filament\Admin\Resources\StorePaymentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateStorePayment extends CreateRecord
{
    protected static string $resource = StorePaymentResource::class;
}
