<?php

declare(strict_types=1);

namespace App\Filament\Admin\Pages;

use BackedEnum;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Support\Icons\Heroicon;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationLabel = 'الرئيسية';
    protected static ?string $title = 'الرئيسية';
    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedHome;
}
