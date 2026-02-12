<?php

namespace App\Filament\Seller\Resources\Orders\Pages;

use App\Filament\Seller\Resources\Orders\OrderResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('print_invoice')
                ->label('طباعة الفاتورة')
                ->icon('heroicon-o-printer')
                ->url(fn () => route('invoice.show', $this->record))
                ->openUrlInNewTab()
                ->color('gray'),
            DeleteAction::make(),
        ];
    }
}
