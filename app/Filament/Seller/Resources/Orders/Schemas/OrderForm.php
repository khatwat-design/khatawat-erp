<?php

namespace App\Filament\Seller\Resources\Orders\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات الزبون')
                    ->schema([
                        TextInput::make('customer_first_name')
                            ->label('الاسم الأول')
                            ->disabled(),
                        TextInput::make('customer_last_name')
                            ->label('اللقب')
                            ->disabled(),
                        TextInput::make('customer_phone')
                            ->label('رقم الهاتف')
                            ->disabled(),
                        Textarea::make('address')
                            ->label('العنوان')
                            ->rows(3)
                            ->disabled()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('تفاصيل الطلب')
                    ->schema([
                        TextInput::make('order_number')
                            ->label('رقم الطلب')
                            ->disabled(),
                        Select::make('status')
                            ->label('الحالة')
                            ->options([
                                'pending' => 'قيد الانتظار',
                                'processing' => 'قيد المعالجة',
                                'shipped' => 'تم الشحن',
                                'completed' => 'مكتمل',
                                'cancelled' => 'ملغي',
                            ])
                            ->required(),
                        TextInput::make('total_amount')
                            ->label('الإجمالي')
                            ->disabled()
                            ->formatStateUsing(fn ($state) => $state === null ? null : number_format((float) $state) . ' د.ع'),
                    ])
                    ->columns(2),
            ]);
    }
}
