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
use Illuminate\Support\Str;

class StoreDomainSettings extends Page
{
    public ?array $data = [];

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-globe-alt';
    protected static ?string $navigationLabel = 'الدومين الخاص';
    protected static ?string $title = 'ربط دومينك الخاص';
    protected static ?string $slug = 'domain-settings';

    protected static bool $shouldRegisterNavigation = false;

    public function mount(): void
    {
        $store = Filament::getTenant();

        if (! $store) {
            abort(404);
        }

        $this->form->fill([
            'custom_domain' => $store->custom_domain ?? '',
            'subdomain' => $store->subdomain ?? '',
            'store_url' => $this->getStoreUrl($store),
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
                Section::make('الدومين الفرعي (الحالي)')
                    ->description('متجرك يعمل حالياً عبر هذا الرابط. يمكنك مشاركته مع العملاء.')
                    ->schema([
                        Placeholder::make('subdomain')
                            ->label('معرف المتجر')
                            ->content(fn () => $this->data['subdomain'] ?? '—'),
                                        Placeholder::make('store_url')
                            ->label('رابط المتجر الحالي')
                            ->content(fn () => $this->data['store_url'] ?? '—'),
                        Action::make('view_store_domain')
                            ->label('عرض المتجر')
                            ->icon('heroicon-o-arrow-top-right-on-square')
                            ->url(fn () => $this->data['store_url'] ?? '#', shouldOpenInNewTab: true)
                            ->visible(fn () => ! empty($this->data['store_url']) && ($this->data['store_url'] ?? '') !== '#'),
                    ])
                    ->columns(1),
                Section::make('ربط دومينك الخاص')
                    ->description('أدخل دومينك مثل shop.example.com. يجب أن يشير السجل A أو CNAME إلى سيرفر المتجر.')
                    ->schema([
                        TextInput::make('custom_domain')
                            ->label('الدومين المخصص')
                            ->placeholder('shop.example.com')
                            ->helperText('أدخل الدومين بدون https://. مثال: mystore.com أو shop.mystore.com')
                            ->maxLength(255)
                            ->rule(function (): \Closure {
                                return function (string $attribute, $value, \Closure $fail) {
                                    if (empty(trim($value))) {
                                        return;
                                    }
                                    $store = Filament::getTenant();
                                    if (! $store) {
                                        return;
                                    }
                                    $existing = \App\Models\Store::query()
                                        ->where('custom_domain', trim($value))
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
                                '<li>ادخل إلى لوحة تحكم الدومين (Domain Registrar)</li>' .
                                '<li>أضف سجل <strong>A</strong>: الاسم = الدومين الفرعي (مثل shop) أو @ للرئيسي، يشير إلى = عنوان IP السيرفر</li>' .
                                '<li>أو سجل <strong>CNAME</strong>: الاسم = shop (مثلاً)، الهدف = store.khtwat.com</li>' .
                                '<li>انتظر حتى 48 ساعة لنشر التغييرات</li>' .
                                '<li>تأكد من إعداد SSL (HTTPS) للسيرفر</li>' .
                                '</ol>' .
                                '</div>'
                            )),
                    ])
                    ->collapsed(),
            ]);
    }

    public function save(): void
    {
        $store = Filament::getTenant();
        if (! $store) {
            abort(404);
        }

        $data = $this->form->getState();
        $domain = isset($data['custom_domain']) ? trim((string) $data['custom_domain']) : null;

        if ($domain === '') {
            $domain = null;
        }

        $store->update(['custom_domain' => $domain]);

        $this->data['store_url'] = $this->getStoreUrl($store->fresh());

        Notification::make()
            ->success()
            ->title('تم حفظ إعدادات الدومين')
            ->send();
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([EmbeddedSchema::make('form')])
                    ->id('domain-settings-form')
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
