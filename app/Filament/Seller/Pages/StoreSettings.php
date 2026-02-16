<?php

namespace App\Filament\Seller\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
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

    protected static ?int $navigationSort = 100;

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
                                            ->disk('public')
                                            ->directory('stores/theme')
                                            ->imagePreviewHeight(120),
                                        FileUpload::make('hero_banner')
                                            ->label('صورة الواجهة الرئيسية')
                                            ->image()
                                            ->disk('public')
                                            ->directory('stores/theme')
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
                                    ->description('إرسال الطلبات تلقائياً إلى جدول Google عبر App Script.')
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
                        Tab::make('إعدادات التوصيل')
                            ->icon('heroicon-o-truck')
                            ->schema([
                                Section::make('تكاليف التوصيل حسب المحافظة')
                                    ->description('حدد تكلفة التوصيل لكل محافظة.')
                                    ->schema([
                                        Action::make('open_shipping')
                                            ->label('إدارة إعدادات التوصيل')
                                            ->icon('heroicon-o-cog-6-tooth')
                                            ->url(fn () => \App\Filament\Seller\Resources\ShippingSettings\ShippingSettingResource::getUrl('index')),
                                    ]),
                            ]),
                        Tab::make('الاشتراك والفواتير')
                            ->icon('heroicon-o-credit-card')
                            ->schema([
                                Section::make('باقتك الحالية')
                                    ->schema([
                                        Placeholder::make('billing_plan')
                                            ->label('الخطة')
                                            ->content(fn () => Filament::getTenant()?->subscription_plan ?? 'غير محدد'),
                                        Placeholder::make('billing_expires')
                                            ->label('تاريخ الانتهاء')
                                            ->content(fn () => Filament::getTenant()?->subscription_expires_at?->format('Y-m-d') ?? '—'),
                                    ])
                                    ->columns(2)
                                    ->headerActions(
                                        class_exists(\App\Models\SubscriptionPlan::class)
                                            ? [
                                                Action::make('renew_subscription')
                                                    ->label('تجديد الاشتراك')
                                                    ->icon('heroicon-o-arrow-path')
                                                    ->form([
                                                        Select::make('plan_id')
                                                            ->label('الباقة')
                                                            ->options(\App\Models\SubscriptionPlan::query()->where('is_active', true)->pluck('name', 'id'))
                                                            ->required(),
                                                        FileUpload::make('proof_url')
                                                            ->label('إيصال الدفع')
                                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'application/pdf'])
                                                            ->maxSize(10240)
                                                            ->disk('public')
                                                            ->directory('payments'),
                                                        Textarea::make('notes')->label('ملاحظات')->rows(2),
                                                    ])
                                                    ->action(function (array $data): void {
                                                        $store = Filament::getTenant();
                                                        if (! $store) {
                                                            return;
                                                        }
                                                        $plan = \App\Models\SubscriptionPlan::find($data['plan_id']);
                                                        \App\Models\StorePayment::create([
                                                            'store_id' => $store->id,
                                                            'subscription_plan_id' => $data['plan_id'],
                                                            'amount' => $plan?->price ?? 0,
                                                            'method' => 'manual',
                                                            'proof_url' => is_array($data['proof_url'] ?? null) ? ($data['proof_url'][0] ?? null) : ($data['proof_url'] ?? null),
                                                            'status' => 'pending',
                                                            'notes' => $data['notes'] ?? null,
                                                        ]);
                                                        Notification::make()->success()->title('تم إرسال طلب التجديد')->body('سيتم مراجعة طلبك قريباً')->send();
                                                    }),
                                            ]
                                            : []
                                    ),
                            ]),
                        Tab::make('الدعم الفني')
                            ->icon('heroicon-o-lifebuoy')
                            ->schema([
                                Section::make('التذاكر والاستفسارات')
                                    ->description('افتح تذكرة دعم أو تابع التذاكر الحالية.')
                                    ->schema([
                                        Action::make('open_support')
                                            ->label('فتح الدعم الفني')
                                            ->icon('heroicon-o-chat-bubble-left-right')
                                            ->url(fn () => \App\Filament\Seller\Resources\SupportTickets\SupportTicketResource::getUrl('index')),
                                    ]),
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

        $store->update([
            'theme_config' => $themeConfig,
            'logo_url' => $themeConfig['store_logo'] ?: $store->logo_url,
            'custom_domain' => $domain,
            'telegram_bot_token' => $telegramToken,
            'telegram_channel_id' => $telegramChannel,
            'google_sheets_webhook_url' => $googleSheetsWebhook,
        ]);

        // تحديث عرض الرابط بعد الحفظ
        $store = $store->fresh();
        $this->data['store_url'] = $this->getStoreUrl($store);
        $this->data['google_sheets_webhook_url'] = $googleSheetsWebhook ?? '';

        Notification::make()
            ->success()
            ->title('تم حفظ الإعدادات')
            ->send();
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
