<?php

namespace App\Enum\V1;

enum RoleName: string
{
    case TIM_TEKNIS = 'tim_teknis';
    case ADMIN_GUDANG = 'admin_gudang_umum';
    case PENANGGUNG_JAWAB = 'penanggung_jawab';
}
