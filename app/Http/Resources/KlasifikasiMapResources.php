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
        } elseif (!$compact) {
            $data['pola_ruang'] = [];
        }

        // struktur_ruang
        if ($this->relationLoaded('strukturRuang') && $this->strukturRuang->isNotEmpty()) {
            $data['struktur_ruang'] = StrukturRuangResources::collection($this->strukturRuang);
        } elseif (!$compact) {
            $data['struktur_ruang'] = [];
        }

        // ketentuan_khusus
        if ($this->relationLoaded('ketentuanKhusus') && $this->ketentuanKhusus->isNotEmpty()) {
            $data['ketentuan_khusus'] = KetentuanKhususResources::collection($this->ketentuanKhusus);
        } elseif (!$compact) {
            $data['ketentuan_khusus'] = [];
        }

        // dokumen
        if ($this->relationLoaded('dokumen') && $this->dokumen->isNotEmpty()) {
            $data['dokumen'] = DokumenResources::collection($this->dokumen);
        } elseif (!$compact) {
            $data['dokumen'] = [];
        }

        // kawasan_strategi_provinsi
        if ($this->relationLoaded('kawasanStrategiProvinsi') && $this->kawasanStrategiProvinsi->isNotEmpty()) {
            $data['kawasan_strategi_provinsi'] = KawasanStrategiProvinsiResources::collection($this->kawasanStrategiProvinsi);
        } elseif (!$compact) {
            $data['kawasan_strategi_provinsi'] = [];
        }

        // data_spasial
        if ($this->relationLoaded('dataSpasial') && $this->dataSpasial->isNotEmpty()) {
            $data['data_spasial'] = DataSpasialResources::collection($this->dataSpasial);
        } elseif (!$compact) {
            $data['data_spasial'] = [];
        }

        // batas_administrasi
        if ($this->relationLoaded('batasAdministrasi') && $this->batasAdministrasi->isNotEmpty()) {
            $data['batas_administrasi'] = \App\Http\Resources\BatasAdministrasiResource::collection($this->batasAdministrasi);
        } elseif (!$compact) {
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
