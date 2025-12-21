<?php

namespace App\Exports;

use App\Repositories\V1\PengeluaranRepository;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PengeluaranExport implements FromCollection, WithHeadings
{
    protected $repository;
    protected $filters;

    public function __construct(PengeluaranRepository $repository, array $filters)
    {
        $this->repository = $repository;
        $this->filters = $filters;
    }

    public function collection()
    {
        return $this->repository
            ->getAllPengeluaranQuery($this->filters)
            ->get();
    }

    public function headings(): array
    {
        return [
            'No Surat',
            'Instalasi',
            'Kategori',
            'Nama Barang',
            'Quantity',
            'Harga',
            'Subtotal',
            'Tanggal Pengeluaran',
        ];
    }
}
