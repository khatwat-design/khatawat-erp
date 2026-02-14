<?php

namespace App\Filament\Seller\Resources\ShippingSettings;

use App\Filament\Seller\Resources\ShippingSettings\Pages\CreateShippingSetting;
use App\Filament\Seller\Resources\ShippingSettings\Pages\EditShippingSetting;
use App\Filament\Seller\Resources\ShippingSettings\Pages\ListShippingSettings;
use App\Filament\Seller\Resources\ShippingSettings\Schemas\ShippingSettingForm;
use App\Filament\Seller\Resources\ShippingSettings\Tables\ShippingSettingsTable;
use App\Models\ShippingSetting;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ShippingSettingResource extends Resource
{
    protected static ?string $model = ShippingSetting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;
    protected static ?string $navigationLabel = 'إعدادات التوصيل';
    protected static ?string $modelLabel = 'إعداد توصيل';
    protected static ?string $pluralModelLabel = 'إعدادات التوصيل';

    public static function getModelLabel(): string
    {
        return 'إعداد توصيل';
    }

    public static function getPluralModelLabel(): string
    {
        return 'إعدادات التوصيل';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'الإعدادات';
    }

    public static function getNavigationSort(): ?int
    {
        return 1;
    }

    public static function form(Schema $schema): Schema
    {
        return ShippingSettingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ShippingSettingsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListShippingSettings::route('/'),
            'create' => CreateShippingSetting::route('/create'),
            'edit' => EditShippingSetting::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $tenant = Filament::getTenant();

        if ($tenant) {
            $query->where('tenant_id', $tenant->id);
        }

        return $query;
    }
}
