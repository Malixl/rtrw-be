<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KlasifikasiMapResources extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Default behavior for map endpoints per Rafiq:
        // - include empty child arrays by default (compact = false)
        // - return ISO timestamps by default (raw_dates = true)
        $compact = filter_var($request->query('compact', false), FILTER_VALIDATE_BOOLEAN);
        $rawDates = filter_var($request->query('raw_dates', true), FILTER_VALIDATE_BOOLEAN);

        $data = [
            'id' => $this->id,
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

        // batas_administrasi
        if ($this->relationLoaded('batasAdministrasi') && $this->batasAdministrasi->isNotEmpty()) {
            $data['batas_administrasi'] = \App\Http\Resources\BatasAdministrasiResource::collection($this->batasAdministrasi);
        } elseif (! $compact) {
            $data['batas_administrasi'] = [];
        }

        // layer_group metadata (if available)
        $data['layer_group'] = $this->relationLoaded('layerGroup') && $this->layerGroup ? [
            'id' => $this->layerGroup->id,
            'layer_group_name' => $this->layerGroup->nama_layer_group ?? $this->layerGroup->layer_group_name ?? null,
        ] : null;

        // timestamps
        $data['created_at'] = $rawDates ? ($this->created_at?->format('c')) : ($this->created_at?->format('d F Y'));
        $data['updated_at'] = $rawDates ? ($this->updated_at?->format('c')) : ($this->updated_at?->format('d F Y'));

        return $data;
    }
}
