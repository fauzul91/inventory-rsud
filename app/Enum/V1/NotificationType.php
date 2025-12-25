<?php

namespace App\Enum\V1;

enum NotificationType: string
{
    case PENERIMAAN_DIAJUKAN = 'penerimaan_diajukan';
    case UPLOAD_TTD_PENERIMAAN = 'upload_ttd_penerimaan';
    case PEMESANAN_DIAJUKAN = 'pemesanan_diajukan';
    case KONFIRMASI_PEMESANAN_ADMIN = 'konfirmasi_pemesanan_admin';
    case STOK_MENIPIS = 'stok_menipis';
}
