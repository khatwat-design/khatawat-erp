<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\StorePaymentResource\Pages;
use App\Models\StorePayment;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class StorePaymentResource extends Resource
{
    protected static ?string $model = StorePayment::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;
    protected static ?string $navigationLabel = 'المدفوعات';
    protected static ?string $modelLabel = 'دفعة';
    protected static ?string $pluralModelLabel = 'المدفوعات';

    public static function getNavigationGroup(): ?string
    {
        return 'الاشتراكات';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('الدفعة')
                    ->schema([
                        Select::make('store_id')
                            ->label('المتجر')
                            ->relationship('store', 'name')
                            ->required()
                            ->searchable(),
                        Select::make('subscription_plan_id')
                            ->label('الباقة')
                            ->relationship('plan', 'name')
                            ->searchable(),
                        \Filament\Forms\Components\TextInput::make('amount')
                            ->label('المبلغ')
                            ->numeric()
                            ->required(),
                        Select::make('method')
                            ->label('طريقة الدفع')
                            ->options(['manual' => 'يدوي', 'zaincash' => 'زين كاش'])
                            ->required(),
                        FileUpload::make('proof_url')
                            ->label('إيصال الدفع')
                            ->disk('public')
                            ->directory('payments'),
                        Select::make('status')
                            ->label('الحالة')
                            ->options(['pending' => 'قيد المراجعة', 'approved' => 'معتمد', 'rejected' => 'مرفوض'])
                            ->required(),
                        \Filament\Forms\Components\DateTimePicker::make('paid_at')->label('تاريخ الدفع'),
                        Textarea::make('notes')->label('ملاحظات')->rows(3),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('store.name')->label('المتجر')->searchable(),
                TextColumn::make('amount')->label('المبلغ')->formatStateUsing(fn ($s) => number_format($s) . ' د.ع'),
                TextColumn::make('method')->label('الطريقة')->badge(),
                TextColumn::make('status')->label('الحالة')->badge()->colors(['warning' => 'pending', 'success' => 'approved', 'danger' => 'rejected']),
                TextColumn::make('paid_at')->label('تاريخ الدفع')->dateTime('Y-m-d H:i'),
            ])
            ->filters([
                SelectFilter::make('status')->label('الحالة')->options(['pending' => 'قيد المراجعة', 'approved' => 'معتمد', 'rejected' => 'مرفوض']),
            ])
            ->recordActions([
                \Filament\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStorePayments::route('/'),
            'create' => Pages\CreateStorePayment::route('/create'),
            'edit' => Pages\EditStorePayment::route('/{record}/edit'),
        ];
    }
}
