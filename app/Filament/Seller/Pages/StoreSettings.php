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

class StoreSettings extends Page
{
    public ?array $data = [];

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'الإعدادات';

    protected static ?string $title = 'إعدادات المتجر';

    protected static ?string $slug = 'settings';

    protected static ?int $navigationSort = 100;

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
                                        TextInput::make('google_sheets_webhook_url')
                                            ->label('رابط Web App (App Script)')
                                            ->placeholder('https://script.google.com/macros/s/xxxxx/exec')
                                            ->url()
                                            ->helperText('لإعداد الربط الكامل (كود + اختبار)، اذهب إلى الإعدادات ← التكاملات.'),
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
