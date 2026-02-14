<?php

namespace App\Filament\Seller\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StoreBilling extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationLabel = 'الاشتراك والفواتير';
    protected static ?string $title = 'باقتك والاشتراك';
    protected static ?string $slug = 'billing';
    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return 'الإعدادات';
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('باقتك الحالية')
                    ->description('معلومات الاشتراك وطلب التجديد')
                    ->schema([
                        \Filament\Schemas\Components\Placeholder::make('plan_name')
                            ->label('الخطة')
                            ->content(fn () => Filament::getTenant()?->subscriptionPlan?->name ?? 'غير محدد'),
                        \Filament\Schemas\Components\Placeholder::make('expires_at')
                            ->label('تاريخ الانتهاء')
                            ->content(fn () => Filament::getTenant()?->subscription_expires_at?->format('Y-m-d') ?? '—'),
                    ])
                    ->columns(2)
                    ->headerActions([
                        Action::make('renew')
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
                                    ->directory('payments')
                                    ->helperText('ارفع صورة إيصال الدفع (حد أقصى 10MB)'),
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
                                Notification::make()
                                    ->success()
                                    ->title('تم إرسال طلب التجديد')
                                    ->body('سيتم مراجعة طلبك قريباً')
                                    ->send();
                            }),
                    ]),
            ]);
    }
}
