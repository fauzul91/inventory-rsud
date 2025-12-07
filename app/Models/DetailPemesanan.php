<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPemesanan extends Model
{
    use HasFactory;

    protected $fillable = ['pemesanan_id', 'stok_id', 'quantity'];

    public function pemesanan()
    {
        return $this->belongsTo(Pemesanan::class);
    }
    public function stok()
    {
        return $this->belongsTo(Stok::class);
    }
}
