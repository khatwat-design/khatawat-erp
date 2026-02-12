<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\StorePaymentResource\Pages;

use App\Filament\Admin\Resources\StorePaymentResource;
use Filament\Resources\Pages\ListRecords;

class ListStorePayments extends ListRecords
{
    protected static string $resource = StorePaymentResource::class;
}
