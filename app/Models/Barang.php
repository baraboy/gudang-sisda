<?php

namespace App\Models;
use App\Enums\SatuanBarang;

use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{

    protected $fillable = [
        'kode',
        'nama',
        'jumlah',
        'kategori',
        'satuan',
        'lokasi',
        'foto',
        'keterangan',
    ];

    protected $casts = [
        'satuan' => SatuanBarang::class,
    ];

    protected static function booted()
    {
        static::creating(function ($barang) {
            if (!$barang->kode) {
                $prefix = $barang->kategori?->kode_kategori ?? 'BRG';

                $count = static::where('kategori_id', $barang->kategori_id)->count() + 1;

                $barang->kode = $prefix . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
            }
        });
    }


    public function stockOpnameItems()
    {
        return $this->hasMany(StockOpnameItem::class);
    }

    public function kategori()
    {
        return $this->belongsTo(Kategori::class);
    }


}
