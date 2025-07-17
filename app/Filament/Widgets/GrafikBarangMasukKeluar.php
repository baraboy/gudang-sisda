<?php

namespace App\Filament\Widgets;

use App\Models\StockOpnameItem;
use App\Models\StockOpname;
use Filament\Forms\Components\Select;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class GrafikBarangMasukKeluar extends ChartWidget
{
    protected static ?string $heading = 'Grafik Barang Masuk / Keluar Mingguan';
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $labels = [];
        $masukData = [];
        $keluarData = [];

        foreach (range(1, 12) as $month) {
            $bulan = Carbon::create(null, $month, 1);
            $labels[] = $bulan->translatedFormat('F'); // contoh: Januari, Februari

            $masuk = StockOpnameItem::whereHas('stockOpname', function ($query) use ($month) {
                $query->whereMonth('tanggal', $month)
                    ->whereYear('tanggal', now()->year)
                    ->where('tipe', 'masuk');
            })->sum('jumlah');

            $keluar = StockOpnameItem::whereHas('stockOpname', function ($query) use ($month) {
                $query->whereMonth('tanggal', $month)
                    ->whereYear('tanggal', now()->year)
                    ->where('tipe', 'keluar');
            })->sum('jumlah');

            $masukData[] = $masuk;
            $keluarData[] = $keluar;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Masuk',
                    'data' => $masukData,
                    'backgroundColor' => '#16a34a',
                ],
                [
                    'label' => 'Keluar',
                    'data' => $keluarData,
                    'backgroundColor' => '#dc2626',
                ],
            ],
            'labels' => $labels,
        ];
    }


    protected function getType(): string
    {
        return 'bar';
    }
}

