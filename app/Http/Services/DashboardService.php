<?php

namespace App\Http\Services;

use App\Models\IndikasiProgram;
use App\Models\KetentuanKhusus;
use App\Models\Pkkprl;
use App\Models\Polaruang;
use App\Models\StrukturRuang;


class DashboardService
{

    public function getCounts()
    {
        return [
            'polaruang'          => Polaruang::count(),
            'struktur_ruang'     => StrukturRuang::count(),
            'ketentuan_khusus'   => KetentuanKhusus::count(),
            'indikasi_program'   => IndikasiProgram::count(),
            'pkkprl'             => Pkkprl::count(),
        ];
    }
}
