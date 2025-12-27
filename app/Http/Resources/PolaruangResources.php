<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PolaruangResources extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'klasifikasi' => [
                'id' => $this->klasifikasi->id ?? null,
                'nama' => $this->klasifikasi->nama ?? null,
                'deskripsi' => $this->klasifikasi->deskripsi ?? null,
                'tipe' => $this->klasifikasi->tipe ?? null,
                'layer_group' => [
                    'id' => optional($this->klasifikasi->layerGroup)->id ?? null,
                    'nama_layer_group' => optional($this->klasifikasi->layerGroup)->nama_layer_group ?? null,
                ],
            ],
            'nama' => $this->nama,
            'warna' => $this->warna,
            'deskripsi' => $this->deskripsi,
            'geojson_file' => $this->geojson_file,
            'created_at' => $this->created_at->format('d F Y'),
            'updated_at' => $this->updated_at->format('d F Y'),
        ];
    }
}
