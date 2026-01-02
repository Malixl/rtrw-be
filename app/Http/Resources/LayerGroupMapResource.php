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

        $types = [
            'pola_ruang',
            'struktur_ruang',
            'ketentuan_khusus',
            'indikasi_program',
            'pkkprl',
            'data_spasial',
            'batas_administrasi',
        ];

        $klasifikasisByType = [];

        foreach ($types as $tipe) {
            $filtered = $klas->filter(fn($k) => $k->tipe === $tipe)->values();
            $key = 'klasifikasi_' . $tipe;
            $klasifikasisByType[$key] = KlasifikasiMapResources::collection($filtered);
        }

        return [
            'id' => $this->id,
            // Keep both keys for backward compatibility: prefer `layer_group_name` as public contract
            'layer_group_name' => $this->layer_group_name ?? $this->nama_layer_group,
            'nama_layer_group' => $this->nama_layer_group ?? $this->layer_group_name,
            'deskripsi' => $this->deskripsi,
            'urutan_tampil' => $this->urutan_tampil,
            'klasifikasis' => $klasifikasisByType,
            'created_at' => $this->created_at?->format('c'),
            'updated_at' => $this->updated_at?->format('c'),
        ];
    }
}
