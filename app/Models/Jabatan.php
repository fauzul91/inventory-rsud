<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Str;

class Jabatan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'slug'
    ];

    public function pegawais()
    {
        return $this->hasMany(Pegawai::class, 'jabatan_id', 'jabatan_id');
    }
    protected static function booted()
    {
        static::creating(function ($jabatan) {
            $jabatan->slug = Str::slug($jabatan->name);
        });

        static::updating(function ($jabatan) {
            $jabatan->slug = Str::slug($jabatan->name);
        });
    }
}