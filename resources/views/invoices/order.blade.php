<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>فاتورة - {{ $order->order_number ?? $order->id }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #333; margin: 0; padding: 20px; }
        .header { text-align: center; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 2px solid #f59e0b; }
        .header h1 { margin: 0 0 5px 0; font-size: 22px; color: #1f2937; }
        .header p { margin: 0; color: #6b7280; font-size: 11px; }
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-table td { padding: 6px 0; vertical-align: top; }
        .info-table .label { color: #6b7280; width: 120px; }
        table.items { width: 100%; border-collapse: collapse; margin: 20px 0; }
        table.items th { background: #f59e0b; color: white; padding: 10px 8px; text-align: right; }
        table.items td { padding: 8px; border-bottom: 1px solid #e5e7eb; }
        table.items tr:nth-child(even) { background: #f9fafb; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .total-row { font-weight: bold; font-size: 14px; background: #fef3c7 !important; }
        .footer { margin-top: 30px; padding-top: 15px; border-top: 1px solid #e5e7eb; font-size: 10px; color: #9ca3af; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $store->name }}</h1>
        <p>فاتورة مبيعات</p>
    </div>

    <table class="info-table">
        <tr>
            <td class="label">رقم الفاتورة:</td>
            <td>{{ $order->order_number ?? '#'.$order->id }}</td>
            <td class="label">التاريخ:</td>
            <td>{{ $order->created_at->format('Y-m-d H:i') }}</td>
        </tr>
        <tr>
            <td class="label">اسم العميل:</td>
            <td>{{ trim(($order->customer_first_name ?? '') . ' ' . ($order->customer_last_name ?? '')) ?: $order->customer_name }}</td>
            <td class="label">الهاتف:</td>
            <td>{{ $order->customer_phone }}</td>
        </tr>
        <tr>
            <td class="label">العنوان:</td>
            <td colspan="3">{{ $order->address ?? '—' }}</td>
        </tr>
        @if($order->tracking_number)
        <tr>
            <td class="label">رقم التتبع:</td>
            <td colspan="3">{{ $order->tracking_number }}</td>
        </tr>
        @endif
    </table>

    <table class="items">
        <thead>
            <tr>
                <th class="text-right">#</th>
                <th class="text-right">المنتج</th>
                <th class="text-right">الكمية</th>
                <th class="text-right">السعر</th>
                <th class="text-right">الإجمالي</th>
            </tr>
        </thead>
        <tbody>
            @php $items = $order->items->count() ? $order->items : collect($order->order_details['items'] ?? []); $i = 1; @endphp
            @forelse($items as $item)
                @php
                    $name = is_object($item) ? ($item->product->name ?? $item->name ?? '') : ($item['name'] ?? '');
                    $qty = is_object($item) ? $item->quantity : ($item['quantity'] ?? 0);
                    $price = is_object($item) ? $item->unit_price : ($item['price'] ?? 0);
                    $total = is_object($item) ? $item->line_total : ($item['line_total'] ?? $qty * $price);
                @endphp
                <tr>
                    <td class="text-right">{{ $i++ }}</td>
                    <td class="text-right">{{ $name }}</td>
                    <td class="text-right">{{ $qty }}</td>
                    <td class="text-right">{{ number_format((float)$price) }} {{ $currency }}</td>
                    <td class="text-right">{{ number_format((float)$total) }} {{ $currency }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-right">لا توجد عناصر</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="4" class="text-right">المجموع الكلي:</td>
                <td class="text-right">{{ number_format((float)$order->total_amount) }} {{ $currency }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        {{ $store->name }} — تم إنشاء هذه الفاتورة تلقائياً من نظام خطوات ERP
    </div>
</body>
</html>
