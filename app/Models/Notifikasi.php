<?php

namespace App\Models;

use App\Enum\V1\NotificationType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notifikasi extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'sender',
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
    protected $appends = ['url'];
    public function getUrlAttribute(): string
    {
        $urlFrontEnd = env('FRONTEND_URL', 'http://localhost:5173');
        return match ($this->type) {
            NotificationType::PENERIMAAN_DIAJUKAN->value => "$urlFrontEnd/penerimaan/inspect/" . ($this->data['penerimaan_id'] ?? ''),
            NotificationType::UPLOAD_TTD_PENERIMAAN->value => "$urlFrontEnd/penerimaan/unggah/" . ($this->data['penerimaan_id'] ?? ''),
            NotificationType::PEMESANAN_DIAJUKAN->value => "$urlFrontEnd/pemesanan/lihat/" . ($this->data['pemesanan_id'] ?? ''),
            NotificationType::KONFIRMASI_PEMESANAN_ADMIN->value => "$urlFrontEnd/pemesanan/lihat/" . ($this->data['pemesanan_id'] ?? ''),
            NotificationType::STOK_MENIPIS->value => "$urlFrontEnd/stok/lihat/" . ($this->data['stok_id'] ?? ''),
            default => "$urlFrontEnd",
        };
    }
}
