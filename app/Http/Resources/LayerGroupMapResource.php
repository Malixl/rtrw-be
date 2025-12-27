<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LayerGroupMapResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Rafiq requested format: each layer group contains klasifikasis grouped by tipe
        $klas = $this->resource->relationLoaded('klasifikasis') ? $this->klasifikasis : collect();

        $pola = $klas->flatMap(fn($k) => $k->polaRuang ?? collect());
        $struktur = $klas->flatMap(fn($k) => $k->strukturRuang ?? collect());
        $ketentuan = $klas->flatMap(fn($k) => $k->ketentuanKhusus ?? collect());
        $pkkprl = $klas->flatMap(fn($k) => $k->pkkprl ?? collect());
        $indikasi = $klas->flatMap(fn($k) => $k->indikasiProgram ?? collect());

        return [
            'id' => $this->id,
            // expose only `layer_group_name` (frontend contract)
            'layer_group_name' => $this->layer_group_name,
            'deskripsi' => $this->deskripsi,
            'urutan_tampil' => $this->urutan_tampil,
            'klasifikasis' => [
                'klasifikasi_pola_ruang' => PolaruangResources::collection($pola),
                'klasifikasi_struktur_ruang' => StrukturRuangResources::collection($struktur),
                'klasifikasi_ketentuan_khusus' => KetentuanKhususResources::collection($ketentuan),
                'klasifikasi_pkkprl' => PkkprlResources::collection($pkkprl),
                'klasifikasi_indikasi_program' => IndikasiProgramResources::collection($indikasi),
            ],
            'created_at' => $this->created_at?->format('d F Y'),
            'updated_at' => $this->updated_at?->format('d F Y'),
        ];
    }
}
