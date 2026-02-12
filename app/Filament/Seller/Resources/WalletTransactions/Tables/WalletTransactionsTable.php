<?php

namespace App\Filament\Seller\Resources\WalletTransactions\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WalletTransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('amount')
                    ->label('المبلغ')
                    ->formatStateUsing(fn ($state) => $state === null ? null : number_format((float) $state) . ' د.ع'),
                TextColumn::make('type')
                    ->label('النوع')
                    ->badge()
                    ->colors([
                        'success' => 'credit',
                        'danger' => 'debit',
                    ])
                    ->formatStateUsing(fn ($state) => $state === 'credit' ? 'إضافة' : 'خصم'),
                TextColumn::make('description')
                    ->label('الوصف')
                    ->wrap(),
                TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([])
            ->toolbarActions([]);
    }
}
