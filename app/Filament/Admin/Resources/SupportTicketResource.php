<?php

namespace App\Filament\Admin\Resources;

use App\Models\SupportTicket;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SupportTicketResource extends Resource
{
    protected static ?string $model = SupportTicket::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;
    protected static ?string $navigationLabel = 'تذاكر الدعم';
    protected static ?string $modelLabel = 'تذكرة';
    protected static ?string $pluralModelLabel = 'التذاكر';

    public static function getNavigationGroup(): ?string
    {
        return 'الدعم';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('التذكرة')
                    ->schema([
                        Select::make('store_id')
                            ->label('المتجر')
                            ->relationship('store', 'name')
                            ->required()
                            ->searchable(),
                        Select::make('user_id')
                            ->label('المُرسل')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable(),
                        TextInput::make('subject')->label('الموضوع')->required(),
                        Textarea::make('message')->label('الرسالة')->rows(4),
                        Select::make('status')
                            ->label('الحالة')
                            ->options(['open' => 'مفتوحة', 'in_progress' => 'قيد المعالجة', 'closed' => 'مغلقة'])
                            ->required(),
                        Select::make('assigned_to_user_id')
                            ->label('المُعيّن لـ')
                            ->relationship('assignedTo', 'name')
                            ->searchable(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('store.name')->label('المتجر')->searchable(),
                TextColumn::make('subject')->label('الموضوع')->searchable()->limit(40),
                TextColumn::make('user.name')->label('المرسل'),
                TextColumn::make('status')->label('الحالة')->badge()->colors(['warning' => 'open', 'info' => 'in_progress', 'success' => 'closed']),
                TextColumn::make('assignedTo.name')->label('المُعيّن'),
            ])
            ->filters([
                SelectFilter::make('status')->label('الحالة')->options(['open' => 'مفتوحة', 'in_progress' => 'قيد المعالجة', 'closed' => 'مغلقة']),
            ])
            ->recordActions([
                \Filament\Actions\ViewAction::make(),
                \Filament\Actions\EditAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Admin\Resources\SupportTicketResource\RelationManagers\RepliesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Admin\Resources\SupportTicketResource\Pages\ListSupportTickets::route('/'),
            'create' => \App\Filament\Admin\Resources\SupportTicketResource\Pages\CreateSupportTicket::route('/create'),
            'view' => \App\Filament\Admin\Resources\SupportTicketResource\Pages\ViewSupportTicket::route('/{record}'),
            'edit' => \App\Filament\Admin\Resources\SupportTicketResource\Pages\EditSupportTicket::route('/{record}/edit'),
        ];
    }
}
