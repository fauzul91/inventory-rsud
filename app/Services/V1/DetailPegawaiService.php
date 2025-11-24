<?php

namespace App\Services\V1;

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
            'alamat_staker' => $pegawai['alamat_staker'] ?? null,
        ]);
    }

    public function syncDetailPegawai($penerimaanId, array $pegawais)
    {
        foreach ($pegawais as $pegawai) {
            $pegawaiId = $pegawai['pegawai_id'];
            $existing = $this->repository->findDetailPegawaiByPegawaiId($penerimaanId, $pegawaiId);

            if ($existing) {
                // Update jika ada perubahan
                if (isset($pegawai['alamat_staker'])) {
                    $this->repository->updateDetailPegawai($existing, [
                        'alamat_staker' => $pegawai['alamat_staker']
                    ]);
                }
            } else {
                // Create new
                $this->createSingle($penerimaanId, $pegawai);
            }
        }
    }
}