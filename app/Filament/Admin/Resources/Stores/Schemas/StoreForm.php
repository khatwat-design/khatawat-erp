<?php

namespace App\Filament\Admin\Resources\Stores\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StoreForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات المتجر')
                    ->schema([
                        TextInput::make('name')
                            ->label('اسم المتجر')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('slug')
                            ->label('المعرّف (Slug)')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        TextInput::make('api_key')
                            ->label('مفتاح API')
                            ->readOnly()
                            ->copyable(),
                        FileUpload::make('logo_url')
                            ->label('شعار المتجر')
                            ->image()
                            ->imageEditor()
                            ->circleCropper()
                            ->maxSize(10240)
                            ->disk('public')
                            ->directory(fn ($record) => $record ? "stores/{$record->id}" : 'stores')
                            ->imageResizeMode('contain')
                            ->imageResizeTargetWidth(512)
                            ->imageResizeTargetHeight(512)
                            ->visibility('public'),
                        Select::make('status')
                            ->label('الحالة')
                            ->options([
                                'active' => 'نشط',
                                'inactive' => 'غير نشط',
                            ])
                            ->required(),
                    ])
                    ->columns(2),
                Section::make('الهوية البصرية')
                    ->statePath('branding_config')
                    ->schema([
                        ColorPicker::make('primary_color')
                            ->label('لون العلامة')
                            ->default('#F97316'),
                        Select::make('currency')
                            ->label('العملة')
                            ->options([
                                'USD' => 'USD',
                                'IQD' => 'IQD',
                                'SAR' => 'SAR',
                            ])
                            ->required(),
                    ])
                    ->columns(2),
                Section::make('التكاملات')
                    ->collapsible()
                    ->schema([
                        TextInput::make('telegram_bot_token')
                            ->label('Telegram Bot Token')
                            ->password()
                            ->revealable(),
                        TextInput::make('telegram_channel_id')
                            ->label('Telegram Channel ID'),
                        TextInput::make('google_sheets_token')
                            ->label('Google Sheets Token')
                            ->password()
                            ->revealable(),
                    ])
                    ->columns(2),
                Section::make('التسويق والتتبع')
                    ->collapsible()
                    ->schema([
                        TextInput::make('facebook_pixel_id')
                            ->label('Facebook Pixel ID'),
                        TextInput::make('tiktok_pixel_id')
                            ->label('TikTok Pixel ID'),
                        TextInput::make('snapchat_pixel_id')
                            ->label('Snapchat Pixel ID'),
                        TextInput::make('google_analytics_id')
                            ->label('Google Analytics / GTM ID'),
                    ])
                    ->columns(2),
                Section::make('تفاصيل الاشتراك')
                    ->collapsible()
                    ->schema([
                        Select::make('subscription_plan_id')
                            ->label('الباقة')
                            ->relationship('subscriptionPlan', 'name')
                            ->searchable(),
                        Select::make('subscription_plan')
                            ->label('الخطة (قديم)')
                            ->options([
                                'monthly' => 'شهري',
                                'yearly' => 'سنوي',
                                'lifetime' => 'مدى الحياة',
                            ]),
                        DatePicker::make('subscription_expires_at')
                            ->label('تاريخ الانتهاء'),
                        Toggle::make('is_active')
                            ->label('الحساب نشط')
                            ->inline(false),
                        TextInput::make('manager_phone')
                            ->label('هاتف المدير'),
                    ])
                    ->columns(2),
                Section::make('الدومين المخصص')
                    ->collapsible()
                    ->schema([
                        TextInput::make('custom_domain')->label('الدومين'),
                        Select::make('custom_domain_status')
                            ->label('حالة الموافقة')
                            ->options([
                                'pending' => 'قيد المراجعة',
                                'approved' => 'معتمد',
                                'rejected' => 'مرفوض',
                            ]),
                    ])
                    ->columns(2),
            ]);
    }
}
