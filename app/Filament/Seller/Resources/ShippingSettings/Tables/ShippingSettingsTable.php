<?php

namespace App\Filament\Seller\Resources\ShippingSettings\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ShippingSettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('governorate')
                    ->label('المحافظة')
                    ->searchable(),
                TextColumn::make('cost')
                    ->label('تكلفة التوصيل')
                    ->formatStateUsing(fn ($state) => $state === null ? null : number_format((float) $state) . ' د.ع'),
                TextColumn::make('created_at')
                    ->label('تاريخ الإضافة')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
