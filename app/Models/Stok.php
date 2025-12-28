<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Stok extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['name', 'category_id', 'minimum_stok', 'satuan_id'];
    protected $casts = [
        'minimum_stok' => 'float',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function satuan()
    {
        return $this->belongsTo(Satuan::class);
    }
    public function detailPenerimaanBarang()
    {
        return $this->hasMany(DetailPenerimaanBarang::class);
    }
}
