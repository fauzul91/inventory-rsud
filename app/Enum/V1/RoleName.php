<?php

namespace App\Enum\V1;

enum RoleName: string
{
    case SUPER_ADMIN = 'super-admin';
    case ADMIN_GUDANG = 'admin-gudang-umum';
    case TIM_TEKNIS = 'tim-teknis';
    case PPK = 'tim-ppk';
    case INSTALASI = 'instalasi';
    case PENANGGUNG_JAWAB = 'penanggung-jawab';
}
