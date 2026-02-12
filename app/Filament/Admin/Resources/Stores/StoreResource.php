<?php

namespace App\Filament\Admin\Resources\Stores;

use App\Filament\Admin\Resources\Stores\Pages\CreateStore;
use App\Filament\Admin\Resources\Stores\Pages\EditStore;
use App\Filament\Admin\Resources\Stores\Pages\ListStores;
use App\Filament\Admin\Resources\Stores\Schemas\StoreForm;
use App\Filament\Admin\Resources\Stores\Tables\StoresTable;
use App\Models\Store;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class StoreResource extends Resource
{
    protected static ?string $model = Store::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static ?string $navigationLabel = 'المتاجر';
    protected static ?string $modelLabel = 'متجر';
    protected static ?string $pluralModelLabel = 'المتاجر';
    protected static ?string $recordTitleAttribute = 'name';
    protected static string|\UnitEnum|null $navigationGroup = 'الإدارة';

    public static function getModelLabel(): string
    {
        return 'متجر';
    }

    public static function getPluralModelLabel(): string
    {
        return 'المتاجر';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'الإدارة';
    }

    public static function form(Schema $schema): Schema
    {
        return StoreForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StoresTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ProductsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStores::route('/'),
            'create' => CreateStore::route('/create'),
            'edit' => EditStore::route('/{record}/edit'),
        ];
    }
}
