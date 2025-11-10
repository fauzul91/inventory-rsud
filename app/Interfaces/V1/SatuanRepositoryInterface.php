<?php

namespace App\Interfaces\V1;

interface SatuanRepositoryInterface
{
    public function getAllSatuan(array $filters);
    public function create(array $data);
    public function edit($id);
    public function update(array $data, $id);
    public function delete($id);
}