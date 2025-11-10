<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailPenerimaanBarang extends Model
{
    protected $fillable = [
        'penerimaan_id',
        'nama_barang',
        'category_id',
        'satuan_id',
        'quantity',
        'harga',
        'total_harga',
        'is_layak'
    ];

    public function penerimaan()
    {
        return $this->belongsTo(Penerimaan::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function satuan()
    {
        return $this->belongsTo(Satuan::class);
    }
}