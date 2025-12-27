<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StrukturRuangRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'nama' => 'required|string',
            'deskripsi' => 'nullable|string',
            'klasifikasi_id' => 'required|integer',
            'tipe_geometri' => 'required|in:polyline,point',
            'icon_titik' => 'nullable|image|mimes:png,jpg,jpeg,webp',
            'tipe_garis' => 'nullable|string',
            'warna' => 'nullable|string',
        ];

        // Validasi file hanya jika ada file yang diupload atau ini adalah request create
        if ($this->hasFile('geojson_file')) {
            $rules['geojson_file'] = 'required|file|extensions:geojson';
        } elseif (! $this->route('id')) {
            // Jika create (tidak ada ID), file wajib
            $rules['geojson_file'] = 'required|file|extensions:geojson';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama wajib diisi.',
            'nama.string' => 'Nama harus berupa teks.',

            'deskripsi.string' => 'Deskripsi harus berupa teks.',

            'klasifikasi_id.required' => 'Klasifikasi wajib diisi.',

            'geojson_file.file' => 'File GeoJSON tidak valid.',
            'geojson_file.extensions' => 'File harus berformat .geojson.',

            'tipe_geometri.required' => 'Tipe geometri wajib dipilih.',
            'tipe_geometri.in' => 'Tipe geometri harus polyline atau point.',

            'icon_titik.string' => 'Icon harus berupa teks.',

            'warna.string' => 'Warna harus berupa teks.',
        ];
    }
}
