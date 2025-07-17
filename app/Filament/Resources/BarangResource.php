<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BarangResource\Pages;
use App\Filament\Resources\BarangResource\RelationManagers;
use App\Models\Barang;
use App\Models\Kategori;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\SelectFilter;
use App\Enums\SatuanBarang;
use Filament\Tables\Actions\Action;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\BarangExport;
use Filament\Support\Enums\IconPosition;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;


class BarangResource extends Resource
{
    protected static ?string $model = Barang::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)->schema([
                    TextInput::make('nama')
                        ->label('Nama Barang')
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function (callable $set, callable $get, $state) {
                            $kategoriId = $get('kategori_id');
                            if (!$kategoriId) return;

                            $kategori = Kategori::find($kategoriId);
                            $prefix = $kategori->kode_kategori ?? 'BRG';

                            $existing = Barang::where('kategori_id', $kategoriId)
                                ->where('nama', $state)
                                ->first();

                            if ($existing) {
                                $set('kode', $existing->kode);
                                return;
                            }

                            $count = Barang::where('kategori_id', $kategoriId)->count() + 1;
                            $kode = $prefix . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);

                            $set('kode', $kode);
                    }),

                    TextInput::make('jumlah')->numeric()->default(0)->required(),
                    Select::make('kategori_id')
                        ->relationship('kategori', 'nama')
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                            $kategori = Kategori::find($state);

                            if (!$kategori) return;

                            $prefix = $kategori->kode_kategori ?? 'BRG';

                            // Cek jika nama sudah diisi
                            $nama = $get('nama');
                            if ($nama) {
                                $existing = Barang::where('kategori_id', $state)
                                    ->where('nama', $nama)
                                    ->first();

                                if ($existing) {
                                    $set('kode', $existing->kode);
                                    return;
                                }
                            }

                            $count = Barang::where('kategori_id', $state)->count() + 1;
                            $kode = $prefix . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);

                            $set('kode', $kode);
                        }),

                    TextInput::make('kode')
                        ->label('Kode Barang')
                        ->required()
                        ->disabled()
                        ->dehydrated(), // supaya tetap disimpan walau disabled
                    Select::make('satuan')
                        ->options(collect(SatuanBarang::cases())->mapWithKeys(fn ($case) => [
                            $case->value => $case->label(),
                        ])->toArray())
                        ->required()
                        ->searchable()
                        ->preload()
                        ->native(false),
                    TextInput::make('lokasi'),
                    FileUpload::make('foto')
                        ->label('Foto')
                        ->image()
                        ->disk('public')
                        ->directory('barang')
                        ->visibility('public')
                        ->preserveFilenames()
                ]),
                Textarea::make('keterangan')->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode')->searchable()->sortable(),
                TextColumn::make('nama')->searchable(),
                TextColumn::make('kategori.nama')
                    ->label('Kategori')
                    ->formatStateUsing(function ($state, $record) {
                        return $state . ' (' . $record->kategori->kode_kategori . ')';
                    })
                    ->sortable()
                    ->searchable(),
                TextColumn::make('jumlah')->sortable(),
                TextColumn::make('satuan'),
                TextColumn::make('lokasi'),
                ImageColumn::make('foto')
                    ->label('Foto')
                    ->disk('public') // WAJIB! agar ambil dari storage/app/public
                    ->circular()
                    ->height(60),
            ])
            ->filters([
                SelectFilter::make('kategori')
                    ->relationship('kategori', 'nama')
                    ->label('Kategori')
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->headerActions([
                Action::make('Export')
                    ->label('Export Excel')
                    ->icon('heroicon-m-arrow-down-tray')
                    ->action(function () {
                        $tanggal = now()->format('d-m-Y'); // atau dmy sesuai preferensimu
                        $filename = "data-barang-{$tanggal}.xlsx";
                        return Excel::download(new BarangExport, $filename);
                    }),
            ])

            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBarangs::route('/'),
            'create' => Pages\CreateBarang::route('/create'),
            'edit' => Pages\EditBarang::route('/{record}/edit'),
        ];
    }
}
