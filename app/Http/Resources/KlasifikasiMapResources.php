<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KlasifikasiMapResources extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'rtrw' => [
                'id' => $this->rtrw->id ?? null,
                'nama' => $this->rtrw->nama ?? null,
                'periode' => [
                    'id' => $this->rtrw->periode->id ?? null,
                    'tahun_mulai' => $this->rtrw->periode->tahun_mulai ?? null,
                    'tahun_akhir' => $this->rtrw->periode->tahun_akhir ?? null,
                ],
            ],
            'nama' => $this->nama,
            'deskripsi' => $this->deskripsi,
            'tipe' => $this->tipe,

            'pola_ruang' => PolaruangResources::collection($this->whenLoaded('polaRuang')),
            'struktur_ruang' => StrukturRuangResources::collection($this->whenLoaded('strukturRuang')),
            'ketentuan_khusus' => KetentuanKhususResources::collection($this->whenLoaded('ketentuanKhusus')),
            'indikasi_program' => IndikasiProgramResources::collection($this->whenLoaded('indikasiProgram')),
            'pkkprl' => PkkprlResources::collection($this->whenLoaded('pkkprl')),
            'data_spasial' => DataSpasialResources::collection($this->whenLoaded('dataSpasial')),
        ];
    }
}
