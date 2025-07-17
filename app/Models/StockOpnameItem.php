<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
 use Illuminate\Validation\ValidationException;

class StockOpnameItem extends Model
{
    protected $fillable = [
        'stock_opname_id',
        'barang_id',
        'jumlah',
        'keterangan'
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }

    public function stockOpname()
    {
        return $this->belongsTo(StockOpname::class);
    }

    protected static function booted()
    {

        static::creating(function ($item) {
            $barang = $item->barang;
            $opname = $item->stockOpname;

            if ($opname->tipe === 'keluar' && $barang->jumlah < $item->jumlah) {
                throw ValidationException::withMessages([
                    'jumlah' => "Stok barang '{$barang->nama}' tidak mencukupi! Stok tersedia: {$barang->jumlah}",
                ]);
            }
        });

        static::created(function ($item) {
            $barang = $item->barang;
            $opname = $item->stockOpname;

            if ($opname->tipe === 'masuk') {
                $barang->jumlah += $item->jumlah;
            } elseif ($opname->tipe === 'keluar') {
                $barang->jumlah -= $item->jumlah;
            }

            $barang->save();
        });

        static::deleted(function ($item) {
            $barang = $item->barang;
            $opname = $item->stockOpname;

            if ($opname->tipe === 'masuk') {
                $barang->jumlah -= $item->jumlah;
            } elseif ($opname->tipe === 'keluar') {
                $barang->jumlah += $item->jumlah;
            }

            $barang->save();
        });
    }


}
