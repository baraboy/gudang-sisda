<?php

namespace App\Filament\Widgets;

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\StockOpname;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Barang', Barang::count()),
            Stat::make('Kategori', Kategori::count()),
            Stat::make('Stock Opname', StockOpname::count()),
        ];
    }
}
