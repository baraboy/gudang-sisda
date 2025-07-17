<?php

namespace App\Filament\Widgets;

use App\Models\Barang;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;

class BarangStokMenipis extends BaseWidget
{
    protected static ?string $heading = 'Barang dengan Stok Menipis';
    protected static ?int $sort = 4;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Barang::where('jumlah', '<=', 5)->orderBy('jumlah')->limit(10)
            )
            ->columns([
                ImageColumn::make('foto')
                    ->disk('public')
                    ->height(40)
                    ->circular(),

                TextColumn::make('nama')
                    ->label('Nama Barang')
                    ->searchable(),

                TextColumn::make('jumlah')
                    ->label('Stok')
                    ->badge()
                    ->color('danger'),

                TextColumn::make('kategori.nama')
                    ->label('Kategori'),
            ]);
    }
}
