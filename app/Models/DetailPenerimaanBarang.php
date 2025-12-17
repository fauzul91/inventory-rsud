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
        'harga',
        'total_harga',
        'is_layak',
        'is_paid'
    ];

    protected $casts = [
        'is_layak' => 'boolean',
        'is_paid' => 'boolean',
    ];
    public function penerimaan()
    {
        return $this->belongsTo(Penerimaan::class);
    }
    public function stok()
    {
        return $this->belongsTo(Stok::class);
    }
    public function detailPemesanans()
    {
        return $this->belongsToMany(
            DetailPemesanan::class,
            'detail_pemesanan_penerimaan',
            'detail_penerimaan_id', // foreign key pivot ke model ini
            'detail_pemesanan_id'   // foreign key pivot ke model lawan
        )->withPivot(['quantity', 'harga', 'subtotal'])
            ->withTimestamps();
    }
}
