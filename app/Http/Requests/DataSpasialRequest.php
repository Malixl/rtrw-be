<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DataSpasialRequest extends FormRequest
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
            'klasifikasi_id' => 'required',
            'tipe_geometri'  => 'required|in:polyline,point,polygon',
            'icon_titik'      => 'nullable|image|mimes:png,jpg,jpeg,webp',
            'tipe_garis'     => 'nullable|string',
            'warna' => 'nullable|string',
        ];

        if ($this->hasFile('geojson_file')) {
            $rules['geojson_file'] = 'required|file|extensions:geojson';
        } elseif (!$this->route('id')) {
            $rules['geojson_file'] = 'required|file|extensions:geojson';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama wajib diisi.',
            'nama.string' => 'Nama harus berupa teks.',
            'deskripsi.string' => 'Deskripsi harus berupa text.',
            'klasifikasi_id' => 'Klasifikasi wajib diisi',
            'geojson_file.file' => 'geojson_file harus berupa file.',
            'geojson_file.mimes' => 'geojson_file harus berformat geojson.',
            'warna.string' => 'warna harus berupa teks.',
        ];
    }
}
