<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Penerimaan extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['no_surat', 'deskripsi', 'user_id', 'status', 'category_id'];

    public function detailPegawai()
    {
        return $this->hasMany(DetailPenerimaanPegawai::class);
    }
    public function bast()
    {
        return $this->hasOne(Bast::class);
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function detailBarang()
    {
        return $this->hasMany(DetailPenerimaanBarang::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}