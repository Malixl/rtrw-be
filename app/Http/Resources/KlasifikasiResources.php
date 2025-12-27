<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KlasifikasiResources extends JsonResource
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
            // 'rtrw' => [
            //     'id' => $this->rtrw->id ?? null,
            //     'nama' => $this->rtrw->nama ?? null,
            //     'periode' => [
            //         'id' => $this->rtrw->periode->id ?? null,
            //         'tahun_mulai' => $this->rtrw->periode->tahun_mulai ?? null,
            //         'tahun_akhir' => $this->rtrw->periode->tahun_akhir ?? null,
            //     ],

            // ],
            'nama' => $this->nama,
            'deskripsi' => $this->deskripsi,
            'tipe' => $this->tipe,
            'layer_group' => [
                'id' => optional($this->layerGroup)->id ?? null,
                'nama_layer_group' => optional($this->layerGroup)->nama_layer_group ?? null,
            ],
            'created_at' => $this->created_at->format('d F Y'),
            'updated_at' => $this->updated_at->format('d F Y'),
        ];
    }
}
