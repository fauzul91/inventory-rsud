<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;

class DetailPenerimaanPegawai extends Model
{
    use HasFactory;

    protected $fillable = ['penerimaan_id', 'pegawai_id', 'alamat_staker', 'urutan'];

    public function penerimaan()
    {
        return $this->belongsTo(Penerimaan::class);
    }
    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class);
    }
}
