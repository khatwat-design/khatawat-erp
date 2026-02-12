<?php

namespace App\Filament\Admin\Resources;

use App\Models\SubscriptionPlan;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

class SubscriptionPlanResource extends Resource
{
    protected static ?string $model = SubscriptionPlan::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;
    protected static ?string $navigationLabel = 'باقات الاشتراك';
    protected static ?string $modelLabel = 'باقة';
    protected static ?string $pluralModelLabel = 'الباقات';
    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): ?string
    {
        return 'الاشتراكات';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات الباقة')
                    ->schema([
                        TextInput::make('name')
                            ->label('الاسم')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, $set) => $set('slug', Str::slug($state))),
                        TextInput::make('slug')
                            ->label('المعرّف')
                            ->required()
                            ->unique(ignoreRecord: true),
                        TextInput::make('price')
                            ->label('السعر (د.ع)')
                            ->numeric()
                            ->required(),
                        TextInput::make('duration_days')
                            ->label('مدة الاشتراك (أيام)')
                            ->numeric()
                            ->default(30)
                            ->required(),
                        Toggle::make('is_active')
                            ->label('نشط')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('الاسم')->searchable(),
                TextColumn::make('slug')->label('المعرّف'),
                TextColumn::make('price')->label('السعر')->formatStateUsing(fn ($s) => number_format($s) . ' د.ع'),
                TextColumn::make('duration_days')->label('المدة (يوم)'),
                IconColumn::make('is_active')->label('نشط')->boolean(),
            ])
            ->recordActions([
                \Filament\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Admin\Resources\SubscriptionPlanResource\Pages\ListSubscriptionPlans::route('/'),
            'create' => \App\Filament\Admin\Resources\SubscriptionPlanResource\Pages\CreateSubscriptionPlan::route('/create'),
            'edit' => \App\Filament\Admin\Resources\SubscriptionPlanResource\Pages\EditSubscriptionPlan::route('/{record}/edit'),
        ];
    }
}
