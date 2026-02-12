<?php

namespace App\Filament\Admin\Resources\SupportTicketResource\RelationManagers;

use App\Models\SupportTicketReply;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RepliesRelationManager extends RelationManager
{
    protected static string $relationship = 'replies';

    protected static ?string $title = 'الردود';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()->schema([
                    Textarea::make('message')->label('الرد')->required()->rows(4),
                ]),
            ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        $data['is_staff_reply'] = true;
        return $data;
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')->label('المرسل'),
                TextColumn::make('message')->label('الرسالة')->limit(60),
                TextColumn::make('is_staff_reply')->label('رد الدعم')->badge()->formatStateUsing(fn ($s) => $s ? 'نعم' : 'لا'),
                TextColumn::make('created_at')->label('التاريخ')->dateTime('Y-m-d H:i'),
            ])
            ->recordActions([
                \Filament\Actions\EditAction::make(),
            ]);
    }
}
