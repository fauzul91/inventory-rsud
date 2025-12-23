<?php

namespace App\Interfaces\V1;

interface BastRepositoryInterface
{
    public function getBastList(array $filters, array $statuses);
    public function findPenerimaan($id);
    public function findBast($id);
    public function createBast($penerimaanId, $filename);
    public function updateSignedBast($bast, $path);
    public function getHistory(array $filters);
}
