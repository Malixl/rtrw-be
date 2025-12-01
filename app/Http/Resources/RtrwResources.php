<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RtrwResources extends JsonResource
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
            'nama' => $this->nama,
            'deskripsi' => $this->deskripsi,
            'periode_id' => $this->periode_id,
            'tahun_mulai' => $this->periode->tahun_mulai ?? null,
            'tahun_akhir' => $this->periode->tahun_akhir ?? null,

            'periode' => [
                'id' => $this->periode->id ?? null,
                'tahun_mulai' => $this->periode->tahun_mulai ?? null,
                'tahun_akhir' => $this->periode->tahun_akhir ?? null,
            ],

        ];
    }
}
