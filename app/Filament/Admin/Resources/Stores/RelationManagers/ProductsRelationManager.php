<?php

namespace App\Filament\Admin\Resources\Stores\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';

    public function form(Schema $schema): Schema
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
                            ->disk('public')
                            ->directory('products/gallery'),
                    ])
                    ->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                ImageColumn::make('image_url')
                    ->label('الصورة')
                    ->disk('public')
                    ->circular(),
                TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable(),
                TextColumn::make('price')
                    ->label('السعر')
                    ->money('USD'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()->slideOver(),
            ])
            ->recordActions([
                EditAction::make()->slideOver(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                //
            ]);
    }
}
