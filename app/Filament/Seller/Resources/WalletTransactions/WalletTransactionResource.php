<?php

namespace App\Filament\Seller\Resources\WalletTransactions;

use App\Filament\Seller\Resources\WalletTransactions\Pages\ListWalletTransactions;
use App\Filament\Seller\Resources\WalletTransactions\Schemas\WalletTransactionForm;
use App\Filament\Seller\Resources\WalletTransactions\Tables\WalletTransactionsTable;
use App\Models\WalletTransaction;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WalletTransactionResource extends Resource
{
    protected static ?string $model = WalletTransaction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWallet;
    protected static ?string $navigationLabel = 'المحفظة المالية';
    protected static ?string $modelLabel = 'معاملة مالية';
    protected static ?string $pluralModelLabel = 'المحفظة المالية';

    public static function getModelLabel(): string
    {
        return 'معاملة مالية';
    }

    public static function getPluralModelLabel(): string
    {
        return 'المحفظة المالية';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'المالية';
    }

    public static function getNavigationBadge(): ?string
    {
        return 'قيد التطوير';
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return WalletTransactionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WalletTransactionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWalletTransactions::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
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
