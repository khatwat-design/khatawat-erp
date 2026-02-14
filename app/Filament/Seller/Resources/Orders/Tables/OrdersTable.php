<?php

namespace App\Filament\Seller\Resources\Orders\Tables;

use App\Enums\OrderStatus;
use App\Filament\Exports\OrderExporter;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\Action;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\ExportBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        $formatMoney = fn ($state) => $state === null ? '—' : number_format((float) $state) . ' د.ع';

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
                TextColumn::make('subtotal')
                    ->label('المجموع الفرعي')
                    ->formatStateUsing(fn ($state, $record) => $formatMoney($state ?? $record?->subtotal ?? 0)),
                TextColumn::make('discount_amount')
                    ->label('الخصم')
                    ->formatStateUsing(function ($state, $record) use ($formatMoney) {
                        $amt = $state ?? $record?->discount_amount ?? 0;
                        return (float) $amt > 0 ? '-' . $formatMoney($amt) : '—';
                    }),
                TextColumn::make('total_amount')
                    ->label('الإجمالي النهائي')
                    ->formatStateUsing(fn ($state) => $formatMoney($state)),
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
                    ->visible(fn ($record) => in_array($record->status?->value ?? (string) ($record->status ?? ''), ['shipped', 'ready_to_ship', 'processing', 'confirmed', 'with_delivery'])),
                EditAction::make(),
            ])
            ->headerActions([
                ExportAction::make()
                    ->exporter(OrderExporter::class)
                    ->label('تصدير الكل'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    ExportBulkAction::make()
                        ->exporter(OrderExporter::class)
                        ->label('تصدير المحدد'),
                    \Filament\Tables\Actions\BulkAction::make('change_status')
                        ->label('تغيير الحالة')
                        ->icon('heroicon-o-arrow-path')
                        ->form([
                            Select::make('status')
                                ->label('الحالة الجديدة')
                                ->options(OrderStatus::options())
                                ->required()
                                ->native(false),
                        ])
                        ->action(fn ($records, array $data) => $records->each->update(['status' => $data['status']]))
                        ->deselectRecordsAfterCompletion(),
                ])->label('إجراءات جماعية'),
            ]);
    }
}
