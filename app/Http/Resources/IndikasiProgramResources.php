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
            ],
            'nama' => $this->nama,
            'file_dokumen' => $this->file_dokumen,
        ];
    }
}
