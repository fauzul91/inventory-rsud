<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Traits\HasRoles;

class Pegawai extends Model
{
    use HasRoles, HasFactory;

    protected $fillable = [
        'name', 'nip', 'phone', 'status', 'jabatan_id'
    ];

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class);
    }
}
