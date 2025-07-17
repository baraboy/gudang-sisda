<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Filament\Widgets\BarangStokMenipis;
use App\Filament\Widgets\DashboardOverview;
use App\Filament\Widgets\RiwayatStockOpname;
use App\Filament\Widgets\GrafikBarangMasukKeluar;

class Dashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.dashboard';

    protected function getHeaderWidgets(): array
    {
        return [
            DashboardOverview::class,
            RiwayatStockOpname::class,
            BarangStokMenipis::class,
            GrafikBarangMasukKeluar::class,
            
        ];
    }
}
