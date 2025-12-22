<?php

namespace App\Exports;

use App\Repositories\V1\PengeluaranRepository;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class PengeluaranSheet implements FromCollection, WithHeadings, WithTitle
{
    protected $repository;
    protected $filters;
    protected $category;

    public function __construct(PengeluaranRepository $repository, array $filters, ?string $category = null)
    {
        $this->repository = $repository;
        $this->filters = $filters;
        $this->category = $category;
    }

    public function collection()
    {
        $query = $this->repository->getAllPengeluaranQuery($this->filters);

        if ($this->category) {
            $query->where('c.name', $this->category);
        }

        return $query->get();
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

    public function title(): string
    {
        return $this->category ?? 'Data Pengeluaran Barang RSUD Balung';
    }
}
