<?php

namespace App\Filament\Seller\Resources\Orders\RelationManagers;

use App\Enums\OrderStatus;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StatusHistoryRelationManager extends RelationManager
{
    protected static string $relationship = 'statusHistory';

    protected static ?string $title = 'سجل حالة الطلب';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('status')
                    ->label('الحالة')
                    ->formatStateUsing(fn (string $state) => OrderStatus::tryFrom($state)?->label() ?? $state),
                TextColumn::make('user.name')
                    ->label('بواسطة')
                    ->placeholder('—'),
                TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([])
            ->recordActions([])
            ->paginated([10, 25, 50]);
    }
}
