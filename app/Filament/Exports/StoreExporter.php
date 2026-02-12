<?php

namespace App\Filament\Exports;

use App\Models\Store;
use Carbon\Carbon;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class StoreExporter extends Exporter
{
    protected static ?string $model = Store::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('name')->label('اسم المتجر'),
            ExportColumn::make('slug')->label('المعرّف'),
            ExportColumn::make('subdomain')->label('الدومين الفرعي'),
            ExportColumn::make('custom_domain')->label('الدومين المخصص'),
            ExportColumn::make('status')->label('الحالة'),
            ExportColumn::make('subscriptionPlan.name')->label('الباقة'),
            ExportColumn::make('subscription_expires_at')
                ->label('تاريخ انتهاء الاشتراك')
                ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->format('Y-m-d') : '—'),
            ExportColumn::make('is_active')
                ->label('نشط')
                ->formatStateUsing(fn ($state) => $state ? 'نعم' : 'لا'),
            ExportColumn::make('created_at')
                ->label('تاريخ الإنشاء')
                ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->format('Y-m-d H:i') : ''),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'تم تصدير ' . Number::format($export->successful_rows) . ' متجر بنجاح.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' فشل تصدير ' . Number::format($failedRowsCount) . ' متجر.';
        }

        return $body;
    }
}
