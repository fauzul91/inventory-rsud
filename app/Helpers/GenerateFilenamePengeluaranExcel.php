<?php

namespace App\Helpers;

use Carbon\Carbon;

class GenerateFilenamePengeluaranExcel
{
    function generateExportFilename(array $filters): string
    {
        $base = 'Laporan Pengeluaran Barang';

        if (!empty($filters['category_name'])) {
            $base .= ' ' . $filters['category_name'];
        }

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $start = Carbon::parse($filters['start_date'])->translatedFormat('d M Y');
            $end = Carbon::parse($filters['end_date'])->translatedFormat('d M Y');

            $base .= " {$start} s.d. {$end}";
        } else {
            $base .= ' ' . now()->translatedFormat('F Y');
        }

        return $base . '.xlsx';
    }
}