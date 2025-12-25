<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KetentuanKhususResources extends JsonResource
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
                'rtrw' => [
                    'id' => $this->klasifikasi->rtrw->id ?? null,
                    'nama' => $this->klasifikasi->rtrw->nama ?? null,
                    'periode' => [
                        'id' => $this->klasifikasi->rtrw->periode->id ?? null,
                        'tahun_mulai' => $this->klasifikasi->rtrw->periode->tahun_mulai ?? null,
                        'tahun_akhir' => $this->klasifikasi->rtrw->periode->tahun_akhir ?? null,
                    ],
                    'deskripsi' => $this->klasifikasi->rtrw->deskripsi ?? null,

                ],
                'layer_group' => [
                    'id' => $this->klasifikasi->layerGroup->id ?? null,
                    'nama_layer_group' => $this->klasifikasi->layerGroup->nama_layer_group ?? null,
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
