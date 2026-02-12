<?php

namespace App\Filament\Seller\Resources\Banners;

use App\Models\Banner;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class BannerResource extends Resource
{
    protected static ?string $model = Banner::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;
    protected static ?string $navigationLabel = 'الإعلانات';
    protected static ?string $modelLabel = 'بانر';

    public static function getEloquentQuery(): Builder
    {
        $tenant = \Filament\Facades\Filament::getTenant();
        return parent::getEloquentQuery()->where('store_id', $tenant?->id);
    }

    public static function getNavigationGroup(): ?string
    {
        return 'التسويق';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('البانر')
                    ->schema([
                        FileUpload::make('image_url')
                            ->label('الصورة')
                            ->image()
                            ->disk('public')
                            ->directory('banners')
                            ->required()
                            ->imagePreviewHeight(150),
                        TextInput::make('link')->label('الرابط (اختياري)')->url(),
                        TextInput::make('position')->label('الموقع')->default('home_hero'),
                        TextInput::make('sort_order')->label('الترتيب')->numeric()->default(0),
                        Toggle::make('is_active')->label('نشط')->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_url')->label('الصورة')->circular()->size(60),
                TextColumn::make('link')->label('الرابط')->limit(30),
                TextColumn::make('position')->label('الموقع'),
                IconColumn::make('is_active')->label('نشط')->boolean(),
            ])
            ->recordActions([
                \Filament\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBanners::route('/'),
            'create' => Pages\CreateBanner::route('/create'),
            'edit' => Pages\EditBanner::route('/{record}/edit'),
        ];
    }
}
