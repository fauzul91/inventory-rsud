<?php

namespace App\Interfaces\V1;

interface PengeluaranRepositoryInterface
{
    public function getAllPengeluaranQuery(array $filters);
    public function getAllPengeluaran(array $filters);
}
