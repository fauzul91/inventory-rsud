<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;

class DetailPenerimaanBarang extends Model
{
    use HasFactory;

    protected $fillable = [
        'penerimaan_id',
        'stok_id',
        'quantity',
        'quantity_layak',
        'quantity_tidak_layak',
        'harga',
        'total_harga',
        'is_paid'
    ];

    public function penerimaan()
    {
        return $this->belongsTo(Penerimaan::class);
    }

    public function stok()
    {
        return $this->belongsTo(Stok::class);
    }
}
