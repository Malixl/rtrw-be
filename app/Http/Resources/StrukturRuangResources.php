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
        $rtrw = optional($klasifikasi->rtrw);
        $periode = optional($rtrw->periode);

        return [
            'id' => $this->id,
            'klasifikasi' => [
                'id' => $klasifikasi->id,
                'nama' => $klasifikasi->nama,
                'deskripsi' => $klasifikasi->deskripsi,
                'tipe' => $klasifikasi->tipe,
                'rtrw' => [
                    'id' => $rtrw->id,
                    'nama' => $rtrw->nama,
                    'periode' => [
                        'id' => $periode->id,
                        'tahun_mulai' => $periode->tahun_mulai,
                        'tahun_akhir' => $periode->tahun_akhir,
                    ],
                    'deskripsi' => $rtrw->deskripsi,

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
