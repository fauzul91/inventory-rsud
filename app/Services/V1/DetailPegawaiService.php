<?php

namespace App\Services\V1;

use App\Models\Penerimaan;
use App\Repositories\V1\PenerimaanRepository;

class DetailPegawaiService
{
    private PenerimaanRepository $repository;

    public function __construct(PenerimaanRepository $repository)
    {
        $this->repository = $repository;
    }

    public function createMultiple($penerimaanId, array $pegawais)
    {
        foreach ($pegawais as $pegawai) {
            $this->createSingle($penerimaanId, $pegawai);
        }
    }

    public function createSingle($penerimaanId, array $pegawai)
    {
        return $this->repository->createDetailPegawai([
            'penerimaan_id' => $penerimaanId,
            'pegawai_id' => $pegawai['pegawai_id'],
            'alamat_staker' => $pegawai['alamat_staker'] ?? '-',
        ]);
    }

    public function syncDetailPegawai($penerimaan, array $pegawais)
    {
        $penerimaanId = $penerimaan instanceof Penerimaan ? $penerimaan->id : $penerimaan;

        foreach ($pegawais as $index => $pegawai) {
            $nomorUrut = $index + 1;

            \App\Models\DetailPenerimaanPegawai::updateOrCreate(
                [
                    'penerimaan_id' => $penerimaanId,
                    'urutan' => $nomorUrut,
                ],
                [
                    'pegawai_id' => $pegawai['pegawai_id'],
                    'alamat_staker' => $pegawai['alamat_staker'] ?? '-',
                ]
            );
        }
    }
}