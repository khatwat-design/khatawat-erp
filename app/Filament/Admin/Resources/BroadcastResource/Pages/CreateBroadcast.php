<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\BroadcastResource\Pages;

use App\Filament\Admin\Resources\BroadcastResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBroadcast extends CreateRecord
{
    protected static string $resource = BroadcastResource::class;
}
