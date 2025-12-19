<?php

namespace App\Interfaces\V1;

interface PelaporanRepositoryInterface
{
    public function getTotalStokBarang();
    public function getTotalBastSigned();
    public function getTotalBarangBelumDibayar();
    public function getPenerimaanPerBulan($year);
    public function getPengeluaranPerBulan($year);
    public function getDashboardInsight();
}
