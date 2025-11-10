<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penerimaan extends Model
{
    protected $fillable = ['no_surat', 'deskripsi'];

    public function pegawai()
    {
        return $this->hasMany(DetailPenerimaanPegawai::class);
    }

    public function barang()
    {
        return $this->hasMany(DetailPenerimaanBarang::class);
    }
}
