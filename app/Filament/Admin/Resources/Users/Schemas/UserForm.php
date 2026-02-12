<?php

namespace App\Filament\Admin\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات المستخدم')
                    ->schema([
                        TextInput::make('name')
                            ->label('الاسم')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('البريد الإلكتروني')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        TextInput::make('password')
                            ->label('كلمة المرور')
                            ->password()
                            ->revealable()
                            ->required(fn (string $context): bool => $context === 'create')
                            ->dehydrated(fn ($state) => filled($state)),
                    ])
                    ->columns(2),
                Section::make('الصلاحيات والانتماء')
                    ->schema([
                        Select::make('store_id')
                            ->label('المتجر')
                            ->relationship('store', 'name')
                            ->searchable()
                            ->nullable(),
                        Select::make('role')
                            ->label('الدور')
                            ->options([
                                'owner' => 'مالك',
                                'manager' => 'مدير',
                                'staff' => 'موظف',
                                'merchant' => 'تاجر',
                            ])
                            ->default('merchant')
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }
}
