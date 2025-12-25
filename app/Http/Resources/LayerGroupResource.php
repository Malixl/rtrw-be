<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LayerGroupResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nama_layer_group' => $this->nama_layer_group,
            'deskripsi' => $this->deskripsi,
            'urutan_tampil' => $this->urutan_tampil,
            'klasifikasis' => KlasifikasiResources::collection($this->whenLoaded('klasifikasis')),
            'created_at' => $this->created_at?->format('d F Y'),
            'updated_at' => $this->updated_at?->format('d F Y'),
        ];
    }
}
