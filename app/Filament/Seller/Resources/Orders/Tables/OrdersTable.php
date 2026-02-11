<?php

namespace App\Filament\Seller\Resources\Orders\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')
                    ->label('رقم الطلب')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('customer_first_name')
                    ->label('اسم العميل')
                    ->getStateUsing(fn ($record) => trim(($record->customer_first_name ?? '') . ' ' . ($record->customer_last_name ?? '')) ?: ($record->customer_name ?? ''))
                    ->searchable([
                        'customer_first_name',
                        'customer_last_name',
                        'customer_name',
                    ]),
                TextColumn::make('customer_phone')
                    ->label('رقم الهاتف')
                    ->searchable(),
                TextColumn::make('total_amount')
                    ->label('الإجمالي')
                    ->formatStateUsing(fn ($state) => $state === null ? null : number_format((float) $state) . ' د.ع'),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'processing',
                        'primary' => 'shipped',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ]),
                TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                //
            ]);
    }
}
