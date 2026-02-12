<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\SubscriptionPlanResource\Pages;

use App\Filament\Admin\Resources\SubscriptionPlanResource;
use Filament\Resources\Pages\ListRecords;

class ListSubscriptionPlans extends ListRecords
{
    protected static string $resource = SubscriptionPlanResource::class;
}
