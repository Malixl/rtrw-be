<?php

namespace App\Http\Services;

use App\Models\Dokumen;
use App\Models\KetentuanKhusus;
use App\Models\KawasanStrategiProvinsi;
use App\Models\Polaruang;
use App\Models\StrukturRuang;

class DashboardService
{
    public function getCounts()
    {
        return [
            'polaruang' => Polaruang::count(),
            'struktur_ruang' => StrukturRuang::count(),
            'ketentuan_khusus' => KetentuanKhusus::count(),
            'dokumen' => Dokumen::count(),
            'kawasan_strategi_provinsi' => KawasanStrategiProvinsi::count(),
        ];
    }
}
