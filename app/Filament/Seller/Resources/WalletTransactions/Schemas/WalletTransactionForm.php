<?php

namespace App\Filament\Seller\Resources\WalletTransactions\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class WalletTransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('تفاصيل المعاملة')
                    ->schema([
                        TextInput::make('amount')
                            ->label('المبلغ')
                            ->disabled(),
                        Select::make('type')
                            ->label('النوع')
                            ->options([
                                'credit' => 'إضافة',
                                'debit' => 'خصم',
                            ])
                            ->disabled(),
                        TextInput::make('description')
                            ->label('الوصف')
                            ->disabled(),
                    ])
                    ->columns(2),
            ]);
    }
}
