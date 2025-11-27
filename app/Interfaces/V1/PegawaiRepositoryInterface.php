<?php

namespace App\Interfaces\V1;

interface PegawaiRepositoryInterface
{
    public function getAllForSelect();
    public function getAll(array $filters = []);
    public function findById($id);
    public function create(array $data);
    public function update(array $data, $id);
    public function toggleStatus($id);
}
