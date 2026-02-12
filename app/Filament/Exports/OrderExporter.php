<?php

namespace App\Filament\Exports;

use App\Enums\OrderStatus;
use App\Models\Order;
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
                ->label('اسم العميل'),
            ExportColumn::make('customer_phone')
                ->label('الهاتف'),
            ExportColumn::make('address')
                ->label('العنوان'),
            ExportColumn::make('total_amount')
                ->label('الإجمالي')
                ->numeric(decimalPlaces: 2),
            ExportColumn::make('status')
                ->label('الحالة')
                ->formatStateUsing(fn ($state) => $state instanceof OrderStatus ? $state->label() : (string) $state),
            ExportColumn::make('tracking_number')
                ->label('رقم التتبع'),
            ExportColumn::make('created_at')
                ->label('التاريخ')
                ->dateTime(),
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
