<?php

namespace App\Filament\Admin\Resources;

use App\Models\Broadcast;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BroadcastResource extends Resource
{
    protected static ?string $model = Broadcast::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;
    protected static ?string $navigationLabel = 'التعميمات';
    protected static ?string $modelLabel = 'تعميم';

    public static function getNavigationGroup(): ?string
    {
        return 'التشغيل';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('التعميم')
                    ->schema([
                        TextInput::make('title')->label('العنوان')->required(),
                        Textarea::make('message')->label('الرسالة')->required()->rows(5),
                        Select::make('type')
                            ->label('النوع')
                            ->options(['info' => 'معلومة', 'warning' => 'تحذير', 'urgent' => 'عاجل'])
                            ->default('info'),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('العنوان')->searchable(),
                TextColumn::make('type')->label('النوع')->badge(),
                TextColumn::make('published_at')->label('تاريخ النشر')->dateTime('Y-m-d H:i'),
            ])
            ->recordActions([
                \Filament\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \Filament\Resources\Pages\ListRecords::route('/'),
            'create' => \Filament\Resources\Pages\CreateRecord::route('/create'),
            'edit' => \Filament\Resources\Pages\EditRecord::route('/{record}/edit'),
        ];
    }
}
