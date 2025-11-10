<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailPenerimaanPegawai extends Model
{
    protected $fillable = ['penerimaan_id', 'pegawai_id', 'alamat_staker'];

    public function penerimaan()
    {
        return $this->belongsTo(Penerimaan::class);
    }
    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class);
    }
}