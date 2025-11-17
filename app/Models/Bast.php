<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bast extends Model
{
    use HasFactory;

    protected $fillable = [
        'penerimaan_id',
        'filename',                 
        'uploaded_signed_file',     
        'uploaded_at',              
    ];

    public function penerimaan()
    {
        return $this->belongsTo(Penerimaan::class);
    }  
}