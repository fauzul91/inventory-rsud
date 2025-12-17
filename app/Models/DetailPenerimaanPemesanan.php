<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailPenerimaanPemesanan extends Model
{
    protected $fillable = ['detail_penerimaan_id', 'detail_pemesanan_id', 'quantity', 'harga', 'subtotal'];

    public function detailPenerimaan()
    {
        return $this->belongsTo(DetailPenerimaanBarang::class);
    }
    public function detailPemesanan()
    {
        return $this->belongsTo(DetailPemesanan::class);
    }
}
