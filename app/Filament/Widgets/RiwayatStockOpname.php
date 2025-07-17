<?php

namespace App\Filament\Widgets;

use App\Models\StockOpname;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables\Columns\TextColumn;

class RiwayatStockOpname extends BaseWidget
{
    protected static ?string $heading = 'Riwayat Stock Opname Terbaru';
    protected static ?int $sort = 3;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                StockOpname::withCount('items')->latest('tanggal')->limit(5)
            )
            ->columns([
                TextColumn::make('kode')
                    ->label('Kode')
                    ->searchable()
                    ->url(fn (StockOpname $record) => route('filament.admin.resources.stock-opnames.edit', $record))
                    ->openUrlInNewTab(false),

                TextColumn::make('tanggal')->label('Tanggal')->date('d M Y'),

                TextColumn::make('tipe')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (string $state) => $state === 'masuk' ? 'success' : 'danger'),

                TextColumn::make('items_count')->label('Jumlah Item')->sortable(),
            ]);
    }

}
