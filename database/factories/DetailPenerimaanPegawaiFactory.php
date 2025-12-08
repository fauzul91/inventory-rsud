<?php

namespace Database\Factories;

use App\Models\DetailPenerimaanPegawai;
use App\Models\Penerimaan;
use App\Models\Pegawai;
use Illuminate\Database\Eloquent\Factories\Factory;

class DetailPenerimaanPegawaiFactory extends Factory
{
    protected $model = DetailPenerimaanPegawai::class;

    public function definition()
    {
        return [
            'penerimaan_id' => Penerimaan::factory(),
            'pegawai_id' => Pegawai::factory(),
            'alamat_staker' => $this->faker->address(),
        ];
    }
}
