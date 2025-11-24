<?php

namespace App\Interfaces\V1;

interface BastRepositoryInterface
{
    public function getUnsignedBast(array $filters);
    public function getSignedBast(array $filters);
    public function findPenerimaan($id);
    public function findBast($id);
    public function createBast($penerimaanId, $filename);
    public function updateSignedBast($bast, $path);
    public function getHistory(array $filters);
}
