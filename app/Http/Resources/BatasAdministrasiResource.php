<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class BatasAdministrasiResource extends JsonResource
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
            'klasifikasi_id' => $this->klasifikasi_id ?? $this->klasifikasi->id ?? null,
            'nama' => $this->nama,
            'deskripsi' => $this->deskripsi,
            'warna' => $this->warna,
            'tipe_geometri' => $this->tipe_geometri,
            'tipe_garis' => $this->tipe_garis,
            'geojson_file' => $this->geojson_file,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
