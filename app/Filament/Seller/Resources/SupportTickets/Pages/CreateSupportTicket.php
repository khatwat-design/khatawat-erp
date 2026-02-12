<?php

namespace App\Filament\Seller\Resources\SupportTickets\Pages;

use App\Filament\Seller\Resources\SupportTickets\SupportTicketResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateSupportTicket extends CreateRecord
{
    protected static string $resource = SupportTicketResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $tenant = Filament::getTenant();
        $user = auth()->user();
        if ($tenant && $user) {
            $data['store_id'] = $tenant->id;
            $data['user_id'] = $user->id;
        }
        return $data;
    }
}
