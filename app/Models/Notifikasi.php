<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notifikasi extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'read_at',
        'completed_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // helper
    public function isRead(): bool
    {
        return !is_null($this->read_at);
    }

    public function isCompleted(): bool
    {
        return !is_null($this->completed_at);
    }
}
