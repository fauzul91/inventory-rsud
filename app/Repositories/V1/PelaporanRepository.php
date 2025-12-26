<?php

namespace App\Repositories\V1;

use App\Models\Penerimaan;
use App\Models\DetailPenerimaanBarang;
use App\Interfaces\V1\PelaporanRepositoryInterface;
use Illuminate\Support\Facades\DB;

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
        return Penerimaan::whereIn('status', ['signed', 'paid'])
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();
    }

    public function getTotalBarangBelumDibayar()
    {
        return (int) DetailPenerimaanBarang::whereHas('penerimaan', function ($q) {
            $q->where('status', '!=', 'paid');
        })->sum('quantity');
    }

    public function getDashboardInsight()
    {
        $now = now();
        $year = $now->year;
        $thisMonth = $now->month;
        $lastMonth = $now->copy()->subMonth()->month;

        $stokThisMonth =
            DB::table('detail_penerimaan_barangs')
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $thisMonth)
                ->sum('quantity')
            -
            DB::table('detail_pemesanan_penerimaan')
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $thisMonth)
                ->sum('quantity');

        $stokLastMonth =
            DB::table('detail_penerimaan_barangs')
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $lastMonth)
                ->sum('quantity')
            -
            DB::table('detail_pemesanan_penerimaan')
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $lastMonth)
                ->sum('quantity');

        $stokChangePercent = $stokLastMonth > 0
            ? round((($stokThisMonth - $stokLastMonth) / $stokLastMonth) * 100)
            : 0;

        $belumBayarThisMonth = DetailPenerimaanBarang::whereHas('penerimaan', function ($q) {
            $q->where('status', '!=', 'paid');
        })
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $thisMonth)
            ->sum('quantity');

        $belumBayarLastMonth = DetailPenerimaanBarang::whereHas('penerimaan', function ($q) {
            $q->where('status', '!=', 'paid');
        })
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $lastMonth)
            ->sum('quantity');

        $belumBayarChangePercent = $belumBayarLastMonth > 0
            ? round((($belumBayarLastMonth - $belumBayarThisMonth) / $belumBayarLastMonth) * 100)
            : 0;

        return [
            'stok_change_percent' => abs($stokChangePercent),
            'stok_change_trend' => $stokChangePercent >= 0 ? 'up' : 'down',

            'belum_dibayar_change_percent' => abs($belumBayarChangePercent),
            'belum_dibayar_change_trend' => $belumBayarChangePercent >= 0 ? 'down' : 'up',
        ];
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
                    'month' => (int) $row->month,
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
                    'month' => (int) $row->month,
                    'total' => (int) $row->total,
                ];
            });
    }
}
