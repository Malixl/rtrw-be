<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DataSpasialResources extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $rawDates = filter_var($request->query('raw_dates', true), FILTER_VALIDATE_BOOLEAN);

        return [
            'id' => $this->id,
            'klasifikasi_id' => $this->klasifikasi_id ?? $this->klasifikasi->id ?? null,
            'klasifikasi' => $this->whenLoaded('klasifikasi', function () {
                return [
                    'id' => $this->klasifikasi->id,
                    'nama' => $this->klasifikasi->nama,
                    'tipe' => $this->klasifikasi->tipe,
                    'deskripsi' => $this->klasifikasi->deskripsi,
                ];
            }),
            'nama' => $this->nama,
            'deskripsi' => $this->deskripsi,
            'geojson_file' => $this->geojson_file,
            'tipe_geometri' => $this->tipe_geometri,
            'icon_titik' => $this->icon_titik,
            'tipe_garis' => $this->tipe_garis,
            'warna' => $this->warna,
            'created_at' => $rawDates ? ($this->created_at?->format('c')) : ($this->created_at?->format('d F Y')),
            'updated_at' => $rawDates ? ($this->updated_at?->format('c')) : ($this->updated_at?->format('d F Y')),
        ];
    }
}
