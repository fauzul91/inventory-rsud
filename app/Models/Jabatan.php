<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
}