<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class StockOpname extends Model
{

    protected $fillable = [
        'kode',
        'tipe',
        'kebutuhan_pekerjaan',
        'tanggal',
        'pic_input',
        'pic_penerima',
        'foto_bukti',
        'keterangan',
    ];
    protected static function booted()
    {
        static::creating(function ($record) {
            if (!$record->kode && $record->tanggal) {
                $tanggal = \Carbon\Carbon::parse($record->tanggal)->format('dmy');
                $random = strtoupper(Str::random(3));
                $record->kode = 'OP' . $tanggal . $random;
            }
        });

        static::created(function ($record) {
            if ($record->foto_bukti) {
                $path = storage_path('app/public/' . $record->foto_bukti);
                $manager = new ImageManager(new Driver());

                $image = $manager->read($path);
                $image->scaleDown(1280); // Resize agar panjang max 1280px
                $image->toJpeg(80)->save($path); // Simpan ulang dengan kompresi
            }
        });

        static::deleting(function ($record) {
            // Hapus foto_bukti jika ada
            if ($record->foto_bukti && Storage::disk('public')->exists($record->foto_bukti)) {
                Storage::disk('public')->delete($record->foto_bukti);
            }
        });
    }
    public function items()
    {
        return $this->hasMany(StockOpnameItem::class);
    }

}
