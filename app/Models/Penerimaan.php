<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penerimaan extends Model
{
    protected $fillable = ['no_surat', 'deskripsi', 'status'];

    public function detailPegawai()
    {
        return $this->hasMany(DetailPenerimaanPegawai::class);
    }

    public function detailBarang()
    {
        return $this->hasMany(DetailPenerimaanBarang::class);
    }
}
