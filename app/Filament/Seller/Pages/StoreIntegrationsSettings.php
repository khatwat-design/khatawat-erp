<?php

namespace App\Filament\Seller\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Placeholder;
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
            'google_sheets_webhook_url' => $store->google_sheets_webhook_url ?? '',
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
                Section::make('Google Sheets')
                    ->description('اربط طلباتك بجداول Google. عند إنشاء طلب جديد، سيتم إرسال البيانات إلى الرابط الذي تضعه.')
                    ->schema([
                        TextInput::make('google_sheets_webhook_url')
                            ->label('رابط Web App (App Script)')
                            ->placeholder('https://script.google.com/macros/s/xxxxx/exec')
                            ->url()
                            ->helperText('الصق رابط Web App بعد نشر السكربت في Google Apps Script.'),
                        Action::make('copy_app_script')
                            ->label('نسخ كود App Script')
                            ->icon('heroicon-o-clipboard-document')
                            ->color('gray')
                            ->action(function (): void {
                                $this->copyAppScriptToClipboard();
                            }),
                        Placeholder::make('google_sheets_instructions')
                            ->label('')
                            ->content(new \Illuminate\Support\HtmlString(
                                $this->getGoogleSheetsInstructionsHtml()
                            )),
                    ])
                    ->columns(1),
            ]);
    }

    public function copyAppScriptToClipboard(): void
    {
        $script = $this->getGoogleSheetsAppScriptCode();
        $encoded = json_encode($script);
        $this->js("navigator.clipboard.writeText({$encoded}).then(function(){new FilamentNotification().success().title('تم نسخ الكود').send();}).catch(function(){new FilamentNotification().danger().title('فشل النسخ').send();});");
    }

    protected function getGoogleSheetsAppScriptCode(): string
    {
        return <<<'SCRIPT'
function doPost(e) {
  try {
    var data = JSON.parse(e.postData.contents);
    var sheet = SpreadsheetApp.getActiveSpreadsheet().getActiveSheet();
    var headers = ['تاريخ الطلب','رقم الطلب','العميل','الهاتف','العنوان','المجموع الفرعي','الخصم','الشحن','الإجمالي','الحالة'];
    if (sheet.getLastRow() === 0) sheet.appendRow(headers);
    var row = [
      data.created_at || '',
      data.order_number || data.order_id,
      data.customer_name || '',
      data.customer_phone || '',
      data.address || '',
      data.subtotal || 0,
      data.discount_amount || 0,
      data.shipping_cost || 0,
      data.total_amount || 0,
      data.status || ''
    ];
    sheet.appendRow(row);
    return ContentService.createTextOutput(JSON.stringify({success: true})).setMimeType(ContentService.MimeType.JSON);
  } catch (err) {
    return ContentService.createTextOutput(JSON.stringify({success: false, error: err.toString()})).setMimeType(ContentService.MimeType.JSON);
  }
}
SCRIPT;
    }

    protected function getGoogleSheetsInstructionsHtml(): string
    {
        $script = $this->getGoogleSheetsAppScriptCode();
        $escapedScript = htmlspecialchars($script, ENT_QUOTES, 'UTF-8');

        return '<div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 p-4 text-sm text-gray-700 dark:text-gray-300 space-y-3">' .
            '<p class="font-medium">كيفية الربط:</p>' .
            '<ol class="list-decimal list-inside space-y-1">' .
            '<li>أنشئ جدول Google جديد</li>' .
            '<li>من القائمة: الإضافات → محرّر السكربتات</li>' .
            '<li>انسخ الكود أدناه والصقه في الملف Code.gs</li>' .
            '<li>احفظ ثم: نشر → نشر كتطبيق ويب → اختر "وصول: أي شخص"</li>' .
            '<li>انسخ رابط النشر والصقه أعلاه</li>' .
            '</ol>' .
            '<details class="mt-3"><summary class="cursor-pointer font-medium text-primary-600">عرض كود App Script</summary>' .
            '<pre class="mt-2 p-3 bg-gray-900 text-gray-100 text-xs rounded overflow-x-auto" dir="ltr"><code>' . $escapedScript . '</code></pre>' .
            '</details></div>';
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
            'google_sheets_webhook_url' => trim($data['google_sheets_webhook_url'] ?? '') ?: null,
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
