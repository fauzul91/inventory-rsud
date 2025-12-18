<?php

namespace App\Repositories\V1;

use App\Models\Penerimaan;
use Illuminate\Support\Facades\DB;
use App\Models\DetailPenerimaanBarang;
use App\Interfaces\V1\PelaporanRepositoryInterface;

class PelaporanRepository implements PelaporanRepositoryInterface
{
    public function getTotalStokBarang()
    {
        $totalMasuk = DB::table('detail_penerimaan_barangs')
            ->sum('quantity');

        $totalKeluar = DB::table('detail_pemesanan_penerimaan')
            ->sum('quantity');

        return $totalMasuk - $totalKeluar;
    }

    public function getTotalBastSigned()
    {
        return Penerimaan::where('status', 'signed')->count();
    }

    public function getTotalBarangBelumDibayar()
    {
        return DetailPenerimaanBarang::whereHas('penerimaan', function ($q) {
            $q->where('status', '!=', 'paid');
        })->sum('quantity');
    }

    public function getPenerimaanPerBulan($year)
    {
        return DB::table('detail_penerimaan_barangs')
            ->selectRaw('MONTH(created_at) as month, SUM(quantity) as total')
            ->whereYear('created_at', $year)
            ->groupByRaw('MONTH(created_at)')
            ->orderByRaw('MONTH(created_at)')
            ->get()
            ->map(function ($row) {
                return [
                    'month' => $row->month,
                    'total' => (int) $row->total,
                ];
            });
    }

    public function getPengeluaranPerBulan($year)
    {
        return DB::table('detail_pemesanan_penerimaan')
            ->selectRaw('MONTH(created_at) as month, SUM(quantity) as total')
            ->whereYear('created_at', $year)
            ->groupByRaw('MONTH(created_at)')
            ->orderByRaw('MONTH(created_at)')
            ->get()
            ->map(function ($row) {
                return [
                    'month' => $row->month,
                    'total' => (int) $row->total,
                ];
            });
    }
}
