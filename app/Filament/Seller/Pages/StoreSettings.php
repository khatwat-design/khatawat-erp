<?php

namespace App\Filament\Seller\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Http;

class StoreSettings extends Page
{
    public ?array $data = [];

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'الإعدادات';

    protected static ?string $title = 'إعدادات المتجر';

    protected static ?string $slug = 'settings';

    protected static ?int $navigationSort = 10;

    public static function getNavigationGroup(): ?string
    {
        return 'الإعدادات';
    }

    public function mount(): void
    {
        $store = Filament::getTenant();

        if (! $store) {
            abort(404);
        }

        $themeConfig = is_array($store->theme_config) ? $store->theme_config : [];
        $integrationsConfig = is_array($store->integrations_config) ? $store->integrations_config : [];

        $this->form->fill([
            // مظهر المتجر
            'primary_color' => $themeConfig['primary_color'] ?? '#000000',
            'store_logo' => $themeConfig['store_logo'] ?? null,
            'hero_banner' => $themeConfig['hero_banner'] ?? null,
            // الدومين
            'custom_domain' => $store->custom_domain ?? '',
            'subdomain' => $store->subdomain ?? '',
            'store_url' => $this->getStoreUrl($store),
            // التكاملات
            'telegram_bot_token' => $store->telegram_bot_token ?? $integrationsConfig['telegram_bot_token'] ?? '',
            'telegram_channel_id' => $store->telegram_channel_id ?? $integrationsConfig['telegram_chat_id'] ?? $integrationsConfig['telegram_channel_id'] ?? '',
            'google_sheets_webhook_url' => $store->google_sheets_webhook_url ?? '',
            // Pixels
            'facebook_pixel_id' => $store->facebook_pixel_id ?? '',
            'tiktok_pixel_id' => $store->tiktok_pixel_id ?? '',
            'google_analytics_id' => $store->google_analytics_id ?? '',
        ]);
    }

    protected function getStoreUrl($store): string
    {
        $base = rtrim(config('app.storefront_url', 'http://187.77.68.2:3000'), '/');

        if (! empty($store->custom_domain)) {
            return 'https://' . trim($store->custom_domain, '/');
        }

        $domain = $store->subdomain ?? $store->domain ?? '';

        return $domain ? $base . '?domain=' . $domain : $base;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Tabs::make('إعدادات')
                    ->tabs([
                        Tab::make('مظهر المتجر')
                            ->icon('heroicon-o-paint-brush')
                            ->schema([
                                Section::make('إعدادات المظهر')
                                    ->description('شعار المتجر، اللون الأساسي، وصورة الواجهة الرئيسية.')
                                    ->schema([
                                        ColorPicker::make('primary_color')
                                            ->label('اللون الأساسي')
                                            ->default('#000000')
                                            ->required(),
                                        FileUpload::make('store_logo')
                                            ->label('شعار المتجر')
                                            ->image()
                                            ->imageEditor()
                                            ->circleCropper()
                                            ->maxSize(10240)
                                            ->disk('public')
                                            ->directory(fn () => 'store-' . (Filament::getTenant()?->id ?? 'shared') . '/theme')
                                            ->imageResizeMode('contain')
                                            ->imageResizeTargetWidth(512)
                                            ->imageResizeTargetHeight(512)
                                            ->imagePreviewHeight(120),
                                        FileUpload::make('hero_banner')
                                            ->label('صورة الواجهة الرئيسية')
                                            ->image()
                                            ->imageEditor()
                                            ->imageEditorAspectRatios([null, '16:9', '4:3', '21:9'])
                                            ->maxSize(10240)
                                            ->disk('public')
                                            ->directory(fn () => 'store-' . (Filament::getTenant()?->id ?? 'shared') . '/theme')
                                            ->imageResizeMode('contain')
                                            ->imageResizeTargetWidth(1920)
                                            ->imageResizeTargetHeight(1080)
                                            ->imagePreviewHeight(200),
                                    ])
                                    ->columns(2),
                            ]),
                        Tab::make('الدومين الخاص')
                            ->icon('heroicon-o-globe-alt')
                            ->schema([
                                Section::make('الدومين الفرعي (الحالي)')
                                    ->description('متجرك يعمل حالياً عبر هذا الرابط.')
                                    ->schema([
                                        Placeholder::make('subdomain')
                                            ->label('معرف المتجر')
                                            ->content(fn () => $this->data['subdomain'] ?? '—'),
                                        Placeholder::make('store_url')
                                            ->label('رابط المتجر الحالي')
                                            ->content(fn () => $this->data['store_url'] ?? '—'),
                                        Action::make('view_store')
                                            ->label('عرض المتجر')
                                            ->icon('heroicon-o-arrow-top-right-on-square')
                                            ->url(fn () => $this->data['store_url'] ?? '#', shouldOpenInNewTab: true)
                                            ->visible(fn () => ! empty($this->data['store_url']) && ($this->data['store_url'] ?? '') !== '#'),
                                    ])
                                    ->columns(1),
                                Section::make('ربط دومينك الخاص')
                                    ->description('أدخل دومينك مثل shop.example.com')
                                    ->schema([
                                        TextInput::make('custom_domain')
                                            ->label('الدومين المخصص')
                                            ->placeholder('shop.example.com')
                                            ->helperText('أدخل الدومين بدون https://')
                                            ->maxLength(255)
                                            ->rule(function (): \Closure {
                                                return function (string $attribute, $value, \Closure $fail) {
                                                    if (empty(trim((string) $value))) {
                                                        return;
                                                    }
                                                    $store = Filament::getTenant();
                                                    if (! $store) {
                                                        return;
                                                    }
                                                    $existing = \App\Models\Store::query()
                                                        ->where('custom_domain', trim((string) $value))
                                                        ->whereKey('!=', $store->id)
                                                        ->exists();
                                                    if ($existing) {
                                                        $fail('هذا الدومين مُستخدم لمتجر آخر.');
                                                    }
                                                };
                                            }),
                                    ])
                                    ->columns(1),
                                Section::make('تعليمات إعداد DNS')
                                    ->schema([
                                        Placeholder::make('dns_help')
                                            ->label('')
                                            ->content(new \Illuminate\Support\HtmlString(
                                                '<div class="text-sm text-gray-600 dark:text-gray-400 space-y-2">' .
                                                '<p><strong>لربط دومينك:</strong></p>' .
                                                '<ol class="list-decimal list-inside space-y-1">' .
                                                '<li>ادخل إلى لوحة تحكم الدومين</li>' .
                                                '<li>أضف سجل <strong>A</strong> أو <strong>CNAME</strong></li>' .
                                                '<li>انتظر حتى 48 ساعة لنشر التغييرات</li>' .
                                                '<li>تأكد من إعداد SSL (HTTPS) للسيرفر</li>' .
                                                '</ol>' .
                                                '</div>'
                                            )),
                                    ])
                                    ->collapsed(),
                            ]),
                        Tab::make('التكاملات')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
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
                                    ->description('إرسال الطلبات تلقائياً إلى جدول Google.')
                                    ->schema([
                                        Action::make('open_google_sheets_modal')
                                            ->label('إظهار كود App Script')
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
                                                        $url = trim($data['google_sheets_webhook_url'] ?? '');
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
                                        TextInput::make('google_sheets_webhook_url')
                                            ->label('رابط Web App (App Script)')
                                            ->placeholder('https://script.google.com/macros/s/xxxxx/exec')
                                            ->url()
                                            ->helperText('انسخ الكود من الزر أعلاه، انشره في Google Apps Script، ثم الصق الرابط هنا.'),
                                    ])
                                    ->columns(1),
                            ]),
                        Tab::make('التسويق والتتبع')
                            ->icon('heroicon-o-chart-bar')
                            ->schema([
                                Section::make('Pixels وتحليلات')
                                    ->description('أدخل معرّفات التتبع للمتجر.')
                                    ->schema([
                                        TextInput::make('facebook_pixel_id')
                                            ->label('Facebook Pixel ID')
                                            ->placeholder('1234567890123456'),
                                        TextInput::make('tiktok_pixel_id')
                                            ->label('TikTok Pixel ID')
                                            ->placeholder('XXXXXXXXXXXX'),
                                        TextInput::make('google_analytics_id')
                                            ->label('Google Analytics / GTM ID')
                                            ->placeholder('G-XXXXXXXXXX أو GTM-XXXXXXX'),
                                    ])
                                    ->columns(1),
                            ]),
                    ]),
            ]);
    }

    public function save(): void
    {
        $store = Filament::getTenant();
        if (! $store) {
            abort(404);
        }

        $data = $this->form->getState();

        // مظهر المتجر
        $themeConfig = [
            'primary_color' => $data['primary_color'] ?? '#000000',
            'store_logo' => $data['store_logo'] ?? null,
            'hero_banner' => $data['hero_banner'] ?? null,
        ];

        // الدومين
        $domain = isset($data['custom_domain']) ? trim((string) $data['custom_domain']) : null;
        if ($domain === '') {
            $domain = null;
        }

        // التكاملات
        $telegramToken = trim($data['telegram_bot_token'] ?? '') ?: null;
        $telegramChannel = trim($data['telegram_channel_id'] ?? '') ?: null;
        $googleSheetsWebhook = trim($data['google_sheets_webhook_url'] ?? '') ?: null;

        // Pixels
        $fbPixel = trim($data['facebook_pixel_id'] ?? '') ?: null;
        $tiktokPixel = trim($data['tiktok_pixel_id'] ?? '') ?: null;
        $gaId = trim($data['google_analytics_id'] ?? '') ?: null;

        $store->update([
            'theme_config' => $themeConfig,
            'logo_url' => $themeConfig['store_logo'] ?: $store->logo_url,
            'custom_domain' => $domain,
            'telegram_bot_token' => $telegramToken,
            'telegram_channel_id' => $telegramChannel,
            'google_sheets_webhook_url' => $googleSheetsWebhook,
            'facebook_pixel_id' => $fbPixel,
            'tiktok_pixel_id' => $tiktokPixel,
            'google_analytics_id' => $gaId,
        ]);

        // تحديث عرض الرابط بعد الحفظ
        $this->data['store_url'] = $this->getStoreUrl($store->fresh());

        Notification::make()
            ->success()
            ->title('تم حفظ الإعدادات')
            ->send();
    }

    protected function getGoogleSheetsModalContent(): string
    {
        $script = $this->getGoogleSheetsAppScriptCode();
        $escaped = htmlspecialchars($script, ENT_QUOTES, 'UTF-8');
        $escapedJson = json_encode($script);

        return '<div class="space-y-5 mb-4" dir="rtl" x-data="{ copied: false }">' .
            '<div class="rounded-xl border-2 border-primary-200 dark:border-primary-900 bg-gradient-to-br from-gray-50 to-primary-50/30 dark:from-gray-900 dark:to-primary-950/20 p-4 shadow-sm">' .
            '<div class="flex items-center justify-between mb-3 gap-3">' .
            '<span class="text-base font-semibold text-gray-800 dark:text-gray-200">كود App Script</span>' .
            '<button type="button" @click="navigator.clipboard.writeText(' . $escapedJson . ').then(() => { copied = true; setTimeout(() => copied = false, 2000); new FilamentNotification().success().title(\'تم النسخ\').send(); })" ' .
            'class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium transition shadow-sm">' .
            '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>' .
            '<span x-text="copied ? \'تم النسخ!\' : \'نسخ الكود\'">نسخ الكود</span>' .
            '</button></div>' .
            '<pre class="p-4 bg-gray-900 dark:bg-gray-950 text-green-400 text-sm rounded-lg overflow-x-auto max-h-64 overflow-y-auto font-mono leading-relaxed" dir="ltr"><code>' . $escaped . '</code></pre>' .
            '</div>' .
            '<div class="rounded-lg bg-blue-50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800 p-4">' .
            '<p class="text-sm font-medium text-blue-900 dark:text-blue-100 mb-2">خطوات الربط:</p>' .
            '<ol class="text-sm text-blue-800 dark:text-blue-200 space-y-1 list-decimal list-inside">' .
            '<li>أنشئ جدول Google جديد</li>' .
            '<li>الإضافات → محرّر السكربتات → الصق الكود أعلاه في Code.gs</li>' .
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
        $this->data['google_sheets_webhook_url'] = $url ?? '';
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

    public function resetLogo(): void
    {
        $data = $this->form->getState();
        $data['store_logo'] = null;
        $this->form->fill($data);

        Notification::make()
            ->success()
            ->title('يمكنك الآن رفع شعار جديد')
            ->send();
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([EmbeddedSchema::make('form')])
                    ->id('store-settings-form')
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Actions::make([
                            Action::make('resetLogo')
                                ->label('استبدال الشعار')
                                ->color('gray')
                                ->action('resetLogo'),
                            Action::make('save')
                                ->label('حفظ الإعدادات')
                                ->submit('save'),
                        ])->alignment('right'),
                    ]),
            ]);
    }
}
