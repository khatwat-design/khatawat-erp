<?php

namespace App\Filament\Seller\Resources\Orders\Tables;

use App\Filament\Exports\OrderExporter;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
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
                    ->copyable()
                    ->default('—'),
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
                    ->formatStateUsing(fn ($state) => $state instanceof \App\Enums\OrderStatus ? $state->label() : ($state ?? '—'))
                    ->badge()
                    ->color(fn ($state) => $state instanceof \App\Enums\OrderStatus ? $state->color() : 'gray'),
                TextColumn::make('tracking_number')
                    ->label('رقم التتبع')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(\App\Enums\OrderStatus::options()),
            ])
            ->recordActions([
                Action::make('add_tracking')
                    ->label('رقم التتبع')
                    ->icon('heroicon-o-truck')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('tracking_number')
                            ->label('رقم التتبع')
                            ->placeholder('أدخل الرقم من شركة الشحن'),
                    ])
                    ->action(function ($record, array $data): void {
                        $record->update(['tracking_number' => $data['tracking_number'] ?? null]);
                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('تم تحديث رقم التتبع')
                            ->send();
                    })
                    ->visible(fn ($record) => in_array($record->status?->value ?? (string) ($record->status ?? ''), ['shipped', 'ready_to_ship', 'processing', 'confirmed'])),
                EditAction::make(),
            ])
            ->headerActions([
                ExportAction::make()
                    ->exporter(OrderExporter::class)
                    ->label('تصدير'),
            ]);
    }
}
