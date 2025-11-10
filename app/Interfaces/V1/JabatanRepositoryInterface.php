<?php

namespace App\Interfaces\V1;

interface JabatanRepositoryInterface
{
    public function getAllJabatan(array $filters);
    public function create(array $data);
    public function edit($id);
    public function update(array $data, $id);
    public function delete($id);
}