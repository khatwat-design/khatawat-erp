<?php

namespace App\Filament\Seller\Resources\Coupons;

use App\Models\Coupon;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;
    protected static ?string $navigationLabel = 'الكوبونات';
    protected static ?string $modelLabel = 'كوبون';

    public static function getEloquentQuery(): Builder
    {
        $tenant = \Filament\Facades\Filament::getTenant();
        return parent::getEloquentQuery()->where('store_id', $tenant?->id);
    }

    public static function getNavigationGroup(): ?string
    {
        return 'التسويق';
    }

    public static function getNavigationSort(): ?int
    {
        return 2;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('الكوبون')
                    ->schema([
                        TextInput::make('code')->label('كود الخصم')->required()->maxLength(50),
                        Select::make('discount_type')
                            ->label('نوع الخصم')
                            ->options(['percentage' => 'نسبة مئوية', 'fixed' => 'مبلغ ثابت'])
                            ->required(),
                        TextInput::make('discount_value')->label('قيمة الخصم')->numeric()->required(),
                        TextInput::make('min_order_amount')->label('أقل طلب (اختياري)')->numeric(),
                        TextInput::make('max_uses')->label('حد الاستخدام (اختياري)')->numeric()->integer(),
                        DateTimePicker::make('expires_at')->label('ينتهي في'),
                        Toggle::make('is_active')->label('نشط')->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->label('الكود')->searchable(),
                TextColumn::make('discount_type')->label('النوع')->badge(),
                TextColumn::make('discount_value')->label('القيمة')->formatStateUsing(fn ($s, $r) => $r->discount_type === 'percentage' ? $s . '%' : number_format($s) . ' د.ع'),
                TextColumn::make('used_count')->label('المستخدم'),
                TextColumn::make('expires_at')->label('ينتهي')->dateTime('Y-m-d'),
                IconColumn::make('is_active')->label('نشط')->boolean(),
            ])
            ->recordActions([
                \Filament\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCoupons::route('/'),
            'create' => Pages\CreateCoupon::route('/create'),
            'edit' => Pages\EditCoupon::route('/{record}/edit'),
        ];
    }
}
