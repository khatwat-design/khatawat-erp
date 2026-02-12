<?php

namespace App\Filament\Seller\Resources\SupportTickets;

use App\Models\SupportTicket;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class SupportTicketResource extends Resource
{
    protected static ?string $model = SupportTicket::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedLifebuoy;
    protected static ?string $navigationLabel = 'الدعم الفني';

    public static function getEloquentQuery(): Builder
    {
        $tenant = \Filament\Facades\Filament::getTenant();
        return parent::getEloquentQuery()->where('store_id', $tenant?->id);
    }

    public static function getNavigationGroup(): ?string
    {
        return 'المساعدة';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('التذكرة')
                    ->schema([
                        TextInput::make('subject')->label('الموضوع')->required(),
                        Textarea::make('message')->label('الرسالة')->required()->rows(5),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('subject')->label('الموضوع')->searchable()->limit(40),
                TextColumn::make('status')->label('الحالة')->badge()->colors(['warning' => 'open', 'info' => 'in_progress', 'success' => 'closed']),
                TextColumn::make('created_at')->label('التاريخ')->dateTime('Y-m-d H:i'),
            ])
            ->filters([
                SelectFilter::make('status')->label('الحالة')->options(['open' => 'مفتوحة', 'in_progress' => 'قيد المعالجة', 'closed' => 'مغلقة']),
            ])
            ->recordActions([
                \Filament\Actions\ViewAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Seller\Resources\SupportTickets\RelationManagers\RepliesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSupportTickets::route('/'),
            'create' => Pages\CreateSupportTicket::route('/create'),
            'view' => Pages\ViewSupportTicket::route('/{record}'),
        ];
    }
}
