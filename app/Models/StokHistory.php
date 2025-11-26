<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StokHistory extends Model
{
    use HasFactory;
    protected $fillable = ['stok_id', 'year', 'quantity', 'used_qty', 'remaining_qty', 'source', 'source_id'];

    public function stok()
    {
        return $this->belongsTo(Stok::class);
    }
    public function histories()
    {
        return $this->hasMany(StokHistory::class)
                    ->orderBy('year')
                    ->orderBy('created_at');
    }
    public function getTotalStokAttribute()
    {
        return $this->histories()->sum('remaining_qty');
    }
}
