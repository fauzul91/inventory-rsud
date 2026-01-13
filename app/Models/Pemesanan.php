<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pemesanan extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'nama_pj_instalasi', 'ruangan', 'status', 'tanggal_pemesanan'];

    public function detailPemesanan()
    {
        return $this->hasMany(DetailPemesanan::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    protected $casts = [
        'tanggal_pemesanan' => 'datetime',
    ];
}
