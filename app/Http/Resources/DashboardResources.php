<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardResources extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'polaruang'        => $this['polaruang'],
            'struktur_ruang'   => $this['struktur_ruang'],
            'ketentuan_khusus' => $this['ketentuan_khusus'],
            'indikasi_program' => $this['indikasi_program'],
            'pkkprl'           => $this['pkkprl'],
        ];
    }
}