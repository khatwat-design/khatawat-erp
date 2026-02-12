<?php

namespace App\Filament\Seller\Resources\Orders\Schemas;

use App\Enums\OrderStatus;
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
                            ->label('حالة الطلب')
                            ->options(OrderStatus::options())
                            ->required()
                            ->native(false),
                        TextInput::make('tracking_number')
                            ->label('رقم التتبع')
                            ->placeholder('أدخل رقم التتبع من شركة الشحن')
                            ->maxLength(255),
                        Textarea::make('seller_notes')
                            ->label('ملاحظات التاجر')
                            ->rows(2)
                            ->placeholder('ملاحظات داخلية')
                            ->columnSpanFull(),
                        TextInput::make('total_amount')
                            ->label('الإجمالي')
                            ->disabled()
                            ->formatStateUsing(fn ($state) => $state === null ? null : number_format((float) $state) . ' د.ع'),
                    ])
                    ->columns(2),
            ]);
    }
}
