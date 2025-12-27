<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StrukturRuangResources extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $klasifikasi = optional($this->klasifikasi);

        return [
            'id' => $this->id,
            'klasifikasi' => [
                'id' => $klasifikasi->id,
                'nama' => $klasifikasi->nama,
                'deskripsi' => $klasifikasi->deskripsi,
                'tipe' => $klasifikasi->tipe,
                'layer_group' => [
                    'id' => optional($klasifikasi->layerGroup)->id,
                    'layer_group_name' => optional($klasifikasi->layerGroup)->layer_group_name,
                ],
            ],
            'nama' => $this->nama,
            'deskripsi' => $this->deskripsi,
            'geojson_file' => $this->geojson_file,
            'tipe_geometri' => $this->tipe_geometri,
            'icon_titik' => $this->icon_titik,
            'tipe_garis' => $this->tipe_garis,
            'warna' => $this->warna,
            'created_at' => $this->created_at->format('d F Y'),
            'updated_at' => $this->updated_at->format('d F Y'),
        ];
    }
}
