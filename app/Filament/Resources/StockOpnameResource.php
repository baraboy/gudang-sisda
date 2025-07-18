<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockOpnameResource\Pages;
use App\Models\StockOpname;
use App\Models\Barang;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StockOpnameResource extends Resource
{
    protected static ?string $model = StockOpname::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Info Transaksi')
                    ->schema([
                        TextInput::make('kode')
                            ->label('Kode')
                            ->required()
                            ->disabled()
                            ->dehydrated(), // supaya walaupun disabled tetap disimpan
                        Select::make('tipe')
                            ->options([
                                'masuk' => 'Barang Masuk',
                                'keluar' => 'Barang Keluar',
                            ])
                            ->required(),
                        TextInput::make('kebutuhan_pekerjaan')->label('Kebutuhan'),
                        DatePicker::make('tanggal')
                            ->label('Tanggal')
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if (!$state) return;

                                // Format tanggal: 10-07-2025 → 10725
                                $tanggal = \Carbon\Carbon::parse($state)->format('dmy');
                                $random = strtoupper(Str::random(3)); // Random huruf kapital
                                $kode = 'OP' . $tanggal . $random;

                                $set('kode', $kode);
                            }),
                        TextInput::make('pic_input')->label('PIC Input')->required(),
                        TextInput::make('pic_penerima')->label('PIC Penerima')->nullable(),
                        FileUpload::make('foto_bukti')
                        ->label('Foto Bukti')
                        ->image()
                        ->imageEditor()
                        ->preserveFilenames()
                        ->directory('bukti'),
                        Textarea::make('keterangan')->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Detail Barang')
                    ->schema([
                        Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Select::make('barang_id')
                                    ->label('Barang')
                                    ->relationship('barang', 'nama')
                                    ->required()
                                    ->searchable()
                                    ->reactive(), // wajib biar bisa ditrigger perubahan

                                TextInput::make('jumlah')
                                    ->label('Jumlah')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1)
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                        $barangId = $get('barang_id');
                                        $barang = Barang::find($barangId);

                                        // Ambil stock opname ID dan tipe
                                        $stockOpnameId = request()->route('record');
                                        $tipe = StockOpname::find($stockOpnameId)?->tipe ?? 'keluar'; // fallback jika create

                                        if ($tipe === 'keluar' && $barang && $state > $barang->jumlah) {
                                            // Reset jumlah
                                            $set('jumlah', null);

                                            Notification::make()
                                                ->title('Stok Tidak Mencukupi')
                                                ->body("Stok tersedia untuk '{$barang->nama}' hanya {$barang->jumlah}")
                                                ->danger()
                                                ->send();
                                        }
                                    }),

                                Textarea::make('keterangan')
                                    ->label('Keterangan')
                                    ->rows(2),
                                ])
                                    ->columns(2)
                                    ->required()
                                    ->minItems(1)
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode')->sortable()->searchable(),
                TextColumn::make('tipe')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'masuk' => 'success',
                        'keluar' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('tanggal')->date(),
                TextColumn::make('pic_input'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListStockOpnames::route('/'),
            'create' => Pages\CreateStockOpname::route('/create'),
            'edit' => Pages\EditStockOpname::route('/{record}/edit'),
        ];
    }
}
