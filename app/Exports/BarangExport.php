<?php

namespace App\Exports;

use App\Models\Barang;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BarangExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Barang::select('kode', 'nama', 'jumlah', 'kategori_id', 'satuan', 'lokasi', 'keterangan')->get();
    }

    public function headings(): array
    {
        return ['Kode', 'Nama', 'Jumlah', 'Kategori ID', 'Satuan', 'Lokasi', 'Keterangan'];
    }
}

