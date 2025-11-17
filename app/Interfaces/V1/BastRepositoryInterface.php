<?php

namespace App\Interfaces\V1;

interface BastRepositoryInterface
{
    public function getUnsignedBast(array $data);
    public function getSignedBast(array $data);
    public function generateBast($id);
    public function uploadBast($id, $file);
    public function downloadUnsignedBast($id);
    public function downloadSignedBast($id);
    public function historyBast(array $filters);
}