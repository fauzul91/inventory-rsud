<?php

namespace App\Interfaces\V1;

interface PengeluaranRepositoryInterface
{
    public function getAllPengeluaran(array $filters);
}
