<?php

namespace App\Repositories\V1;

use App\Models\Penerimaan;
use App\Models\DetailPenerimaanBarang;
use App\Interfaces\V1\PelaporanRepositoryInterface;
use Illuminate\Support\Facades\DB;

class PelaporanRepository implements PelaporanRepositoryInterface
{
    public function getDashboardStats()
    {
        $insight = $this->getDashboardInsight();

        return [
            'total_stok_barang' => $this->getTotalStokBarang(),
            'stok_change_percent' => $insight['stok_change_percent'],
            'stok_change_trend' => $insight['stok_change_trend'],
            'bast_sudah_diterima' => $this->getTotalBastSigned(),
            'barang_belum_dibayar' => $this->getTotalBarangBelumDibayar(),
            'belum_dibayar_change_percent' => $insight['belum_dibayar_change_percent'],
            'belum_dibayar_change_trend' => $insight['belum_dibayar_change_trend'],
        ];
    }

    public function getPenerimaanPerBulan($year)
    {
        $data = DB::table('detail_penerimaan_barangs')
            ->selectRaw('MONTH(created_at) as month, SUM(quantity) as total')
            ->whereYear('created_at', $year)
            ->groupByRaw('MONTH(created_at)')
            ->get();

        return $this->fillMissingMonths($data);
    }

    public function getPengeluaranPerBulan($year)
    {
        $data = DB::table('detail_pemesanan_penerimaan')
            ->selectRaw('MONTH(created_at) as month, SUM(quantity) as total')
            ->whereYear('created_at', $year)
            ->groupByRaw('MONTH(created_at)')
            ->get();

        return $this->fillMissingMonths($data);
    }
    private function fillMissingMonths($data)
    {
        return collect(range(1, 12))->map(function ($m) use ($data) {
            $found = $data->firstWhere('month', $m);
            return [
                'month' => (int) $m,
                'total' => (int) ($found->total ?? 0),
            ];
        })->values();
    }
    public function getTotalStokBarang()
    {
        $totalMasuk = DB::table('detail_penerimaan_barangs')
            ->join('penerimaans', 'detail_penerimaan_barangs.penerimaan_id', '=', 'penerimaans.id')
            ->whereIn('penerimaans.status', ['confirmed', 'signed', 'paid'])
            ->sum('detail_penerimaan_barangs.quantity');

        $totalKeluar = DB::table('detail_pemesanan_penerimaan')
            ->join('detail_pemesanans', 'detail_pemesanan_penerimaan.detail_pemesanan_id', '=', 'detail_pemesanans.id')
            ->join('pemesanans', 'detail_pemesanans.pemesanan_id', '=', 'pemesanans.id')
            ->where('pemesanans.status', 'approved_admin_gudang')
            ->sum('detail_pemesanan_penerimaan.quantity');

        return max(0, (int) ($totalMasuk - $totalKeluar));
    }

    public function getTotalBastSigned()
    {
        return Penerimaan::whereIn('status', ['signed', 'paid'])
            ->count();
    }
    public function getTotalBarangBelumDibayar()
    {
        return (int) DetailPenerimaanBarang::where('is_paid', false)
            ->whereHas('penerimaan', function ($q) {
                $q->whereIn('status', ['confirmed', 'signed']);
            })
            ->sum('quantity');
    }

    public function getDashboardInsight()
    {
        $now = now();
        $year = $now->year;
        $thisMonth = $now->month;
        $lastMonth = $now->copy()->subMonth()->month;
        $stokThisMonth = DB::table('detail_penerimaan_barangs')->whereYear('created_at', $year)->whereMonth('created_at', $thisMonth)->sum('quantity') - DB::table('detail_pemesanan_penerimaan')->whereYear('created_at', $year)->whereMonth('created_at', $thisMonth)->sum('quantity');
        $stokLastMonth = DB::table('detail_penerimaan_barangs')->whereYear('created_at', $year)->whereMonth('created_at', $lastMonth)->sum('quantity') - DB::table('detail_pemesanan_penerimaan')->whereYear('created_at', $year)->whereMonth('created_at', $lastMonth)->sum('quantity');
        $stokChangePercent = $stokLastMonth > 0 ? round((($stokThisMonth - $stokLastMonth) / $stokLastMonth) * 100) : 0;
        $belumBayarThisMonth = DetailPenerimaanBarang::whereHas('penerimaan', function ($q) {
            $q->where('status', '!=', 'paid');
        })->whereYear('created_at', $year)->whereMonth('created_at', $thisMonth)->sum('quantity');
        $belumBayarLastMonth = DetailPenerimaanBarang::whereHas('penerimaan', function ($q) {
            $q->where('status', '!=', 'paid');
        })->whereYear('created_at', $year)->whereMonth('created_at', $lastMonth)->sum('quantity');
        $belumBayarChangePercent = $belumBayarLastMonth > 0 ? round((($belumBayarLastMonth - $belumBayarThisMonth) / $belumBayarLastMonth) * 100) : 0;
        return [
            'stok_change_percent' => abs($stokChangePercent),
            'stok_change_trend' => $stokChangePercent >= 0 ? 'up' : 'down',
            'belum_dibayar_change_percent' => abs($belumBayarChangePercent),
            'belum_dibayar_change_trend' => $belumBayarChangePercent >= 0 ? 'down' : 'up',
        ];
    }
}