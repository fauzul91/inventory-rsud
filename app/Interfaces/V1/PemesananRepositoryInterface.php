<?php

namespace App\Interfaces\V1;

interface PemesananRepositoryInterface
{
    public function getAllPemesanan(array $filters, string $status);
    public function createPemesanan(array $data);
    public function getPemesananById($id);
    public function updateQuantityPenanggungJawab(int $pemesananId, int $detailId, int $amount);
}
