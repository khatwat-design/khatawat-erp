<?php

namespace App\Filament\Seller\Resources\Orders\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product.name')
            ->columns([
                TextColumn::make('product.name')
                    ->label('المنتج')
                    ->searchable(),
                TextColumn::make('quantity')
                    ->label('الكمية'),
                TextColumn::make('unit_price')
                    ->label('سعر الوحدة')
                    ->formatStateUsing(fn ($state) => $state === null ? null : number_format((float) $state) . ' د.ع'),
                TextColumn::make('line_total')
                    ->label('الإجمالي')
                    ->formatStateUsing(fn ($state) => $state === null ? null : number_format((float) $state) . ' د.ع'),
            ])
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([]);
    }

    public function isReadOnly(): bool
    {
        return true;
    }
}
