<?php

namespace App\Models;
use App\Enums\SatuanBarang;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

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

        static::created(function ($barang) {
            if ($barang->foto) {
                $path = storage_path('app/public/' . $barang->foto);
                $manager = new ImageManager(new Driver());

                $image = $manager->read($path);
                $image->scaleDown(1280); // Resize agar panjang max 1280px
                $image->toJpeg(80)->save($path); // Simpan ulang dengan kompresi
            }
        });

        // Auto-delete foto dari storage saat barang dihapus
        static::deleting(function ($barang) {
            if ($barang->foto && Storage::disk('public')->exists($barang->foto)) {
                Storage::disk('public')->delete($barang->foto);
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
