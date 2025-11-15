<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Stok extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['name', 'category_id', 'stok_2024', 'minimum_stok', 'total_stok', 'price', 'satuan_id'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function satuan()
    {
        return $this->belongsTo(Satuan::class);
    }
}
