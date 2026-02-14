<?php

namespace App\Filament\Seller\Resources\ShippingSettings\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ShippingSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('إعدادات التوصيل')
                    ->schema([
                        Select::make('governorate')
                            ->label('المحافظة')
                            ->options([
                                'بغداد' => 'بغداد',
                                'البصرة' => 'البصرة',
                                'نينوى' => 'نينوى',
                                'أربيل' => 'أربيل',
                                'النجف' => 'النجف',
                                'كربلاء' => 'كربلاء',
                                'واسط' => 'واسط',
                                'بابل' => 'بابل',
                                'ديالى' => 'ديالى',
                                'الأنبار' => 'الأنبار',
                                'ميسان' => 'ميسان',
                                'الديوانية' => 'الديوانية',
                                'ذي قار' => 'ذي قار',
                                'المثنى' => 'المثنى',
                                'كركوك' => 'كركوك',
                                'صلاح الدين' => 'صلاح الدين',
                                'دهوك' => 'دهوك',
                                'السليمانية' => 'السليمانية',
                            ])
                            ->searchable()
                            ->required(),
                        TextInput::make('cost')
                            ->label('تكلفة التوصيل')
                            ->numeric()
                            ->required()
                            ->suffix('د.ع'),
                    ])
                    ->columns(2),
            ]);
    }
}
