<?php

namespace App\Interfaces\V1;

use App\Models\Penerimaan;
use App\Models\DetailPenerimaanBarang;
use App\Models\DetailPenerimaanPegawai;

interface PenerimaanRepositoryInterface
{
    public function getPenerimaanForTable(array $filters = [], array $statuses = null);
    public function findById($id);
    public function findWithDetails($id);
    public function create(array $data);
    public function update(Penerimaan $penerimaan, array $data);
    public function delete(Penerimaan $penerimaan);
    public function hasUnassessedItems($penerimaanId);
    public function createDetailBarang(array $data);
    public function updateDetailBarang(DetailPenerimaanBarang $detail, array $data);
    public function deleteDetailBarang(array $ids);
    public function findDetailBarang($penerimaanId, $detailId);
    public function createDetailPegawai(array $data);
    public function updateDetailPegawai(DetailPenerimaanPegawai $detail, array $data);
    public function findDetailPegawaiByPegawaiId($penerimaanId, $pegawaiId);
}
