<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Satuan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'slug'
    ];
    protected static function booted()
    {
        static::creating(function ($satuan) {
            $satuan->slug = Str::slug($satuan->name);
        });

        static::updating(function ($satuan) {
            $satuan->slug = Str::slug($satuan->name);
        });
    }
}
