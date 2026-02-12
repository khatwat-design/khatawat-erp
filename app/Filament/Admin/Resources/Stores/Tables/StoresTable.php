<?php

namespace App\Filament\Admin\Resources\Stores\Tables;

use App\Filament\Exports\StoreExporter;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;

class StoresTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('اسم المتجر')
                    ->searchable(),
                TextColumn::make('subdomain')
                    ->label('الدومين الفرعي')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->colors([
                        'success' => 'active',
                        'gray' => 'inactive',
                    ]),
                TextColumn::make('subscriptionPlan.name')
                    ->label('الباقة')
                    ->badge()
                    ->default('—'),
                TextColumn::make('subscription_expires_at')
                    ->label('تاريخ الانتهاء')
                    ->date('Y-m-d')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d H:i'),
            ])
            ->filters([
                Filter::make('expired')
                    ->label('المنتهية')
                    ->query(fn ($query) => $query
                        ->whereNotNull('subscription_expires_at')
                        ->whereDate('subscription_expires_at', '<', Carbon::now()->toDateString())),
            ])
            ->recordActions([
                Action::make('toggle_active')
                    ->label(fn ($record) => $record->is_active ? 'إيقاف' : 'تفعيل')
                    ->icon(fn ($record) => $record->is_active ? 'heroicon-o-pause-circle' : 'heroicon-o-play-circle')
                    ->color(fn ($record) => $record->is_active ? 'warning' : 'success')
                    ->requiresConfirmation()
                    ->modalHeading(fn ($record) => $record->is_active ? 'إيقاف المتجر' : 'تفعيل المتجر')
                    ->action(function ($record): void {
                        $record->update(['is_active' => ! $record->is_active]);
                        Notification::make()
                            ->success()
                            ->title($record->is_active ? 'تم تفعيل المتجر' : 'تم إيقاف المتجر')
                            ->send();
                    }),
                EditAction::make(),
            ])
            ->headerActions([
                ExportAction::make()
                    ->exporter(StoreExporter::class)
                    ->label('تصدير المتاجر'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
