<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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
    }
    public function items()
    {
        return $this->hasMany(StockOpnameItem::class);
    }

}
