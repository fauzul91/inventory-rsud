<?php

namespace App\Interfaces\V1;

interface JabatanRepositoryInterface
{
    public function getAllJabatan(array $filters);
}