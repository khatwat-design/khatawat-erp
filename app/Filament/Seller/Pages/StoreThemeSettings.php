<?php

namespace App\Filament\Seller\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StoreThemeSettings extends Page
{
    public ?array $data = [];

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-paint-brush';
    protected static ?string $navigationLabel = 'مظهر المتجر';
    protected static ?string $title = 'مظهر المتجر';
    protected static ?string $slug = 'theme-settings';

    protected static bool $shouldRegisterNavigation = false;

    public function mount(): void
    {
        $store = Filament::getTenant();

        if (! $store) {
            abort(404);
        }

        $themeConfig = is_array($store->theme_config) ? $store->theme_config : [];

        $this->form->fill([
            'primary_color' => $themeConfig['primary_color'] ?? '#000000',
            'store_logo' => $themeConfig['store_logo'] ?? null,
            'hero_banner' => $themeConfig['hero_banner'] ?? null,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('إعدادات المظهر')
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
            ]);
    }

    public function save(): void
    {
        $store = Filament::getTenant();
        if (! $store) {
            abort(404);
        }

        $data = $this->form->getState();

        $themeConfig = [
            'primary_color' => $data['primary_color'] ?? '#000000',
            'store_logo' => $data['store_logo'] ?? null,
            'hero_banner' => $data['hero_banner'] ?? null,
        ];

        $store->update([
            'theme_config' => $themeConfig,
            // Keep logo_url in sync so storefront always reads latest logo.
            'logo_url' => $themeConfig['store_logo'] ?: $store->logo_url,
        ]);

        Notification::make()
            ->success()
            ->title('تم حفظ إعدادات المظهر')
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
                    ->id('theme-settings-form')
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Actions::make([
                            Action::make('resetLogo')
                                ->label('استبدال الشعار')
                                ->color('gray')
                                ->action('resetLogo'),
                            Action::make('save')
                                ->label('حفظ التغييرات')
                                ->submit('save'),
                        ])->alignment('right'),
                    ]),
            ]);
    }
}
