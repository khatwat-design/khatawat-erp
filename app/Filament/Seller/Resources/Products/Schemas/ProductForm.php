<?php

namespace App\Filament\Seller\Resources\Products\Schemas;

use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('المعلومات الأساسية')
                    ->schema([
                        TextInput::make('name')
                            ->label('اسم المنتج')
                            ->required()
                            ->maxLength(255),
                        RichEditor::make('description')
                            ->label('الوصف')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('التسعير والوسائط')
                    ->schema([
                        TextInput::make('price')
                            ->label('السعر')
                            ->numeric()
                            ->prefix('$')
                            ->required(),
                        FileUpload::make('image_url')
                            ->label('الصورة الرئيسية')
                            ->image()
                            ->disk('public')
                            ->directory('products')
                            ->dehydrated(fn ($state) => filled($state)),
                        FileUpload::make('gallery')
                            ->label('معرض الصور')
                            ->multiple()
                            ->reorderable()
                            ->image()
                            ->imageEditor()
                            ->maxSize(10240)
                            ->disk('public')
                            ->directory(fn () => 'store-' . (Filament::getTenant()?->id ?? 'shared') . '/products/gallery')
                            ->imageResizeMode('contain')
                            ->imageResizeTargetWidth(1920)
                            ->imageResizeTargetHeight(1920),
                    ])
                    ->columns(2),
            ]);
    }
}
