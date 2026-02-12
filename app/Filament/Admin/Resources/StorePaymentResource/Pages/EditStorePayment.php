<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\StorePaymentResource\Pages;

use App\Filament\Admin\Resources\StorePaymentResource;
use Filament\Resources\Pages\EditRecord;

class EditStorePayment extends EditRecord
{
    protected static string $resource = StorePaymentResource::class;
}
