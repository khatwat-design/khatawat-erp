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
use Illuminate\Support\Facades\Http;

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
                            ->helperText('اضغط على زر إعداد Google Sheets لنسخ الكود وإعداد الربط.'),
                        Action::make('open_google_sheets_modal')
                            ->label('إعداد Google Sheets')
                            ->icon('heroicon-o-clipboard-document')
                            ->color('gray')
                            ->modalHeading('ربط Google Sheets')
                            ->modalWidth('2xl')
                            ->modalSubmitActionLabel('حفظ')
                            ->modalCancelActionLabel('إغلاق')
                            ->fillForm(fn (): array => [
                                'google_sheets_webhook_url' => Filament::getTenant()?->google_sheets_webhook_url ?? '',
                            ])
                            ->form([
                                Placeholder::make('app_script_code')
                                    ->label('')
                                    ->content(new \Illuminate\Support\HtmlString(
                                        $this->getGoogleSheetsModalContent()
                                    )),
                                TextInput::make('google_sheets_webhook_url')
                                    ->label('رابط Web App (بعد النشر)')
                                    ->placeholder('https://script.google.com/macros/s/xxxxx/exec')
                                    ->url()
                                    ->live(onBlur: true),
                            ])
                            ->extraModalFooterActions([
                                Action::make('test_google_sheets')
                                    ->label('اختبار الإرسال')
                                    ->icon('heroicon-o-signal')
                                    ->color('gray')
                                    ->action(function (): void {
                                        $data = $this->mountedActions[0]['data'] ?? [];
                                        $url = trim((string) ($data['google_sheets_webhook_url'] ?? ''));
                                        if (empty($url)) {
                                            Notification::make()->danger()->title('أدخل الرابط أولاً')->send();

                                            return;
                                        }
                                        $ok = $this->testGoogleSheetsWebhook($url);
                                        if ($ok) {
                                            Notification::make()->success()->title('تم الإرسال بنجاح! تحقق من الجدول.')->send();
                                        } else {
                                            Notification::make()->danger()->title('فشل الاختبار. تحقق من الرابط والإعدادات.')->send();
                                        }
                                    }),
                            ])
                            ->action(function (array $data): void {
                                $this->saveGoogleSheetsFromModal($data);
                            }),
                    ])
                    ->columns(1),
            ]);
    }

    protected function getGoogleSheetsModalContent(): string
    {
        $script = $this->getGoogleSheetsAppScriptCode();
        $escaped = htmlspecialchars($script, ENT_QUOTES, 'UTF-8');

        return '<div class="space-y-5 mb-4" dir="rtl">' .
            '<div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-900 dark:bg-gray-950 p-5 shadow-inner">' .
            '<p class="text-sm font-medium text-gray-400 dark:text-gray-500 mb-3">كود App Script</p>' .
            '<pre class="p-4 bg-gray-950 dark:bg-black text-green-400 text-sm rounded-lg overflow-x-auto max-h-80 overflow-y-auto font-mono leading-relaxed" dir="ltr"><code>' . $escaped . '</code></pre>' .
            '</div>' .
            '<div class="rounded-lg bg-blue-50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800 p-4">' .
            '<p class="text-sm font-medium text-blue-900 dark:text-blue-100 mb-2">خطوات الربط:</p>' .
            '<ol class="text-sm text-blue-800 dark:text-blue-200 space-y-1 list-decimal list-inside">' .
            '<li>أنشئ جدول Google جديد</li>' .
            '<li>الإضافات → محرّر السكربتات → انسخ الكود أعلاه والصقه في Code.gs</li>' .
            '<li>احفظ (Ctrl+S) ثم: نشر → نشر كتطبيق ويب → "وصول: أي شخص"</li>' .
            '<li>انسخ رابط النشر والصقه في الحقل أدناه</li>' .
            '</ol></div>' .
            '</div>';
    }

    public function saveGoogleSheetsFromModal(array $data): void
    {
        $store = Filament::getTenant();
        if (! $store) {
            return;
        }
        $url = trim($data['google_sheets_webhook_url'] ?? '') ?: null;
        $store->update(['google_sheets_webhook_url' => $url]);
        $this->form->fill(['google_sheets_webhook_url' => $url ?? '']);
        Notification::make()->success()->title('تم الحفظ')->send();
        $this->unmountAction();
    }

    public function testGoogleSheetsWebhook(string $url): bool
    {
        $store = Filament::getTenant();
        if (! $store || empty($url)) {
            return false;
        }
        $payload = [
            'store_id' => $store->id,
            'store_name' => $store->name,
            'order_id' => 0,
            'order_number' => 'TEST-' . time(),
            'customer_name' => 'عميل تجريبي',
            'customer_phone' => '07xx',
            'address' => 'عنوان تجريبي',
            'subtotal' => 10000,
            'discount_amount' => 0,
            'shipping_cost' => 2000,
            'total_amount' => 12000,
            'status' => 'pending',
            'items' => [['product_id' => 0, 'name' => 'منتج تجريبي', 'price' => 10000, 'quantity' => 1, 'line_total' => 10000]],
            'created_at' => now()->toIso8601String(),
        ];
        try {
            $response = Http::timeout(10)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($url, $payload);

            return $response->successful();
        } catch (\Throwable) {
            return false;
        }
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
