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
        $penerimaanId = $penerimaan instanceof Penerimaan
            ? $penerimaan->id
            : (is_object($penerimaan) ? $penerimaan->id : $penerimaan);

        $requestDetailIds = collect($pegawais)
            ->pluck('id')
            ->filter()
            ->toArray();

        $existingPegawais = $this->repository->getDetailPegawaisByPenerimaanId($penerimaanId);

        foreach ($existingPegawais as $existing) {
            if (!empty($requestDetailIds)) {
                if (!in_array($existing->id, $requestDetailIds)) {
                    $this->repository->deleteDetailPegawai($existing);
                }
            } else {
                $requestPegawaiIds = collect($pegawais)->pluck('pegawai_id')->toArray();
                if (!in_array($existing->pegawai_id, $requestPegawaiIds)) {
                    $this->repository->deleteDetailPegawai($existing);
                }
            }
        }

        foreach ($pegawais as $pegawai) {
            $pegawaiData = [
                'alamat_staker' => $pegawai['alamat_staker'] ?? '-'
            ];

            if (!empty($pegawai['id'])) {
                $existing = $existingPegawais->firstWhere('id', $pegawai['id']);

                if ($existing) {
                    $this->repository->updateDetailPegawai($existing, $pegawaiData);
                } else {
                    $this->createSingle($penerimaanId, $pegawai);
                }
            } else {
                $existing = $this->repository->findDetailPegawaiByPegawaiId(
                    $penerimaanId,
                    $pegawai['pegawai_id']
                );

                if ($existing) {
                    $this->repository->updateDetailPegawai($existing, $pegawaiData);
                } else {
                    $this->createSingle($penerimaanId, $pegawai);
                }
            }
        }
    }
}