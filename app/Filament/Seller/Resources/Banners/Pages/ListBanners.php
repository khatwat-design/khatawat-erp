<?php

namespace App\Filament\Seller\Resources\Banners\Pages;

use App\Filament\Seller\Resources\Banners\BannerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBanners extends ListRecords
{
    protected static string $resource = BannerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('إضافة بانر')
                ->icon('heroicon-o-plus'),
        ];
    }
}
