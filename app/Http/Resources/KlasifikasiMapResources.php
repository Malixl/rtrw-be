<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KlasifikasiMapResources extends JsonResource
{
    public function toArray(Request $request): array
    {
        $compact = filter_var($request->query('compact', true), FILTER_VALIDATE_BOOLEAN);

        $data = [
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
        ];

        // pola_ruang
        if ($this->relationLoaded('polaRuang') && $this->polaRuang->isNotEmpty()) {
            $data['pola_ruang'] = PolaruangResources::collection($this->polaRuang);
        } elseif (! $compact) {
            $data['pola_ruang'] = [];
        }

        // struktur_ruang
        if ($this->relationLoaded('strukturRuang') && $this->strukturRuang->isNotEmpty()) {
            $data['struktur_ruang'] = StrukturRuangResources::collection($this->strukturRuang);
        } elseif (! $compact) {
            $data['struktur_ruang'] = [];
        }

        // ketentuan_khusus
        if ($this->relationLoaded('ketentuanKhusus') && $this->ketentuanKhusus->isNotEmpty()) {
            $data['ketentuan_khusus'] = KetentuanKhususResources::collection($this->ketentuanKhusus);
        } elseif (! $compact) {
            $data['ketentuan_khusus'] = [];
        }

        // indikasi_program
        if ($this->relationLoaded('indikasiProgram') && $this->indikasiProgram->isNotEmpty()) {
            $data['indikasi_program'] = IndikasiProgramResources::collection($this->indikasiProgram);
        } elseif (! $compact) {
            $data['indikasi_program'] = [];
        }

        // pkkprl
        if ($this->relationLoaded('pkkprl') && $this->pkkprl->isNotEmpty()) {
            $data['pkkprl'] = PkkprlResources::collection($this->pkkprl);
        } elseif (! $compact) {
            $data['pkkprl'] = [];
        }

        // data_spasial
        if ($this->relationLoaded('dataSpasial') && $this->dataSpasial->isNotEmpty()) {
            $data['data_spasial'] = DataSpasialResources::collection($this->dataSpasial);
        } elseif (! $compact) {
            $data['data_spasial'] = [];
        }

        return $data;
    }
}
