<?php

namespace App\Filament\Exports;

use App\Enums\OrderStatus;
use App\Models\Order;
use Carbon\Carbon;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class OrderExporter extends Exporter
{
    protected static ?string $model = Order::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('order_number')
                ->label('رقم الطلب'),
            ExportColumn::make('customer_name')
                ->label('اسم العميل')
                ->formatStateUsing(fn ($state, Order $record) => trim((string) ($state ?? '')) ?: trim(($record->customer_first_name ?? '') . ' ' . ($record->customer_last_name ?? '')) ?: '—'),
            ExportColumn::make('customer_phone')
                ->label('الهاتف'),
            ExportColumn::make('address')
                ->label('العنوان'),
            ExportColumn::make('total_amount')
                ->label('الإجمالي')
                ->formatStateUsing(fn ($state) => $state !== null ? number_format((float) $state, 2) : ''),
            ExportColumn::make('status')
                ->label('الحالة')
                ->formatStateUsing(fn ($state) => $state instanceof OrderStatus ? $state->label() : (string) $state),
            ExportColumn::make('tracking_number')
                ->label('رقم التتبع'),
            ExportColumn::make('created_at')
                ->label('التاريخ')
                ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->format('Y-m-d H:i') : ''),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'تم تصدير ' . Number::format($export->successful_rows) . ' طلب بنجاح.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' فشل تصدير ' . Number::format($failedRowsCount) . ' طلب.';
        }

        return $body;
    }
}
