<?php

namespace App\Interfaces\V1;

interface PenerimaanRepositoryInterface
{
    public function getAllPenerimaan(array $filters);
    public function create(array $data);
    public function edit($id);
    public function update(array $data, $id);
    public function delete($id);
    public function markBarangLayak($detailId, $isLayak);
    public function confirmPenerimaan($id);
    public function getHistoryPenerimaan(array $filters);
}