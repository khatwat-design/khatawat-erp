<?php

namespace App\Filament\Seller\Resources\SupportTickets\Pages;

use App\Filament\Seller\Resources\SupportTickets\SupportTicketResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSupportTickets extends ListRecords
{
    protected static string $resource = SupportTicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('فتح تذكرة جديدة')
                ->icon('heroicon-o-plus'),
        ];
    }
}
