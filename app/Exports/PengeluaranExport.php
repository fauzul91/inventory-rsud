<?php

namespace App\Exports;

use App\Repositories\V1\PengeluaranRepository;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PengeluaranExport implements WithMultipleSheets
{
    protected $repository;
    protected $filters;
    protected $categories = [
        'ATK',
        'Cetak',
        'Alat Listrik',
        'Bahan Komputer',
        'Kertas dan Cover',
        'Bahan Bangunan',
        'Bahan Pembersih',
    ];

    public function __construct(PengeluaranRepository $repository, array $filters)
    {
        $this->repository = $repository;
        $this->filters = $filters;
    }

    public function sheets(): array
    {
        $sheets = [];

        $allFilters = $this->filters;
        unset($allFilters['category_name']);
        $sheets[] = new PengeluaranSheet($this->repository, $allFilters);

        foreach ($this->categories as $category) {
            $sheets[] = new PengeluaranSheet($this->repository, $this->filters, $category);
        }

        return $sheets;
    }
}
