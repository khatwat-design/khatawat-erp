<?php

namespace App\Filament\Seller\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StoreIntegrationsSettings extends Page
{
    public ?array $data = [];

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'التكاملات';
    protected static ?string $title = 'إعدادات التكاملات';
    protected static ?string $slug = 'integrations-settings';

    protected static bool $shouldRegisterNavigation = false;

    public function mount(): void
    {
        $store = Filament::getTenant();

        if (! $store) {
            abort(404);
        }

        $config = is_array($store->integrations_config) ? $store->integrations_config : [];

        $this->form->fill([
            'telegram_bot_token' => $store->telegram_bot_token ?? $config['telegram_bot_token'] ?? '',
            'telegram_channel_id' => $store->telegram_channel_id ?? $config['telegram_chat_id'] ?? $config['telegram_channel_id'] ?? '',
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Telegram')
                    ->description('استقبل إشعاراً عند كل طلب جديد في قناتك أو مجموعة تيليجرام.')
                    ->schema([
                        TextInput::make('telegram_bot_token')
                            ->label('Bot Token')
                            ->placeholder('123456:ABC-DEF...')
                            ->password()
                            ->revealable()
                            ->helperText('أنشئ بوت عبر @BotFather واحصل على التوكن'),
                        TextInput::make('telegram_channel_id')
                            ->label('Channel ID أو Chat ID')
                            ->placeholder('-1001234567890')
                            ->helperText('معرف القناة أو المجموعة (يبدأ بـ - للقنات/المجموعات)'),
                    ])
                    ->columns(1),
            ]);
    }

    public function save(): void
    {
        $store = Filament::getTenant();
        if (! $store) {
            abort(404);
        }

        $data = $this->form->getState();

        $store->update([
            'telegram_bot_token' => trim($data['telegram_bot_token'] ?? '') ?: null,
            'telegram_channel_id' => trim($data['telegram_channel_id'] ?? '') ?: null,
        ]);

        Notification::make()
            ->success()
            ->title('تم حفظ إعدادات التكاملات')
            ->send();
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([EmbeddedSchema::make('form')])
                    ->id('integrations-form')
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Actions::make([
                            Action::make('save')
                                ->label('حفظ')
                                ->submit('save'),
                        ])->alignment('right'),
                    ]),
            ]);
    }
}
