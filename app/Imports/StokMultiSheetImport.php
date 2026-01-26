<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class StokMultiSheetImport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            'ATK' => new StokPerSheetImport('ATK'),
            'Kertas' => new StokPerSheetImport('Kertas dan Cover'),
            'Pembersih' => new StokPerSheetImport('Bahan Pembersih'),
            'Cetak' => new StokPerSheetImport('Cetak'),
            'Listrik' => new StokPerSheetImport('Alat Listrik'),
            'Bangunan' => new StokPerSheetImport('Bahan Bangunan'),
            'Komputer' => new StokPerSheetImport('Bahan Komputer'),
        ];
    }

    public function sheet($sheet)
    {
        $namaKategori = $sheet->getTitle();

        return new StokPerSheetImport($namaKategori);
    }
}
