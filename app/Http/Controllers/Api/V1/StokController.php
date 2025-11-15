<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Interfaces\V1\StokRepositoryInterface;
use App\Repositories\V1\StokRepository;
use Illuminate\Http\Request;

class StokController extends Controller
{
    private StokRepository $stokRepository;

    public function __construct(StokRepositoryInterface $stokRepository)
    {
        $this->stokRepository = $stokRepository;
    }
}
