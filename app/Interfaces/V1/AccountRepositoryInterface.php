<?php

namespace App\Interfaces\V1;

interface AccountRepositoryInterface
{
    public function getAllAccount(array $filters);
    public function edit($id);
    public function update(array $data, $id);
}