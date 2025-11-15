<?php

namespace App\Interfaces\V1;

interface BastRepositoryInterface
{
    public function generateBast($id);
    public function uploadBast($id, $file);
    public function downloadBast($id);
    public function historyBast();
}