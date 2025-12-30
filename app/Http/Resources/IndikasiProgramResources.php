<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IndikasiProgramResources extends JsonResource
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
            'file_dokumen' => $this->file_dokumen,
        ];
    }
}
