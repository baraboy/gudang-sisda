<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

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
