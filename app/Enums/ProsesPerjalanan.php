<?php

namespace App\Enums;

enum ProsesPerjalanan: string
{
    case BELUM_DIMULAI = 'Belum Dimulai';
    case  DALAM_PERJALANAN = 'Dalam Perjalanan';
    case   SELESAI = "Selesai";
}
