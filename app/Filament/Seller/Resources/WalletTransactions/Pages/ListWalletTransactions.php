<?php

namespace App\Filament\Seller\Resources\WalletTransactions\Pages;

use App\Filament\Seller\Resources\WalletTransactions\WalletTransactionResource;
use Filament\Resources\Pages\ListRecords;

class ListWalletTransactions extends ListRecords
{
    protected static string $resource = WalletTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getTableEmptyStateHeading(): ?string
    {
        return 'قيد التطوير';
    }

    protected function getTableEmptyStateDescription(): ?string
    {
        return 'المحفظة المالية قيد التطوير حالياً. ستتوفر قريباً.';
    }

    protected function getTableEmptyStateIcon(): ?string
    {
        return 'heroicon-o-wrench-screwdriver';
    }
}
