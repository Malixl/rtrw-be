<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PolaruangRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'nama' => 'required|string',
            'deskripsi' => 'nullable|string',
            'klasifikasi_id' => 'required',
            'warna' => 'required|string',
        ];

        // Validasi file hanya jika ada file yang diupload atau ini adalah request create
        if ($this->hasFile('geojson_file')) {
            $rules['geojson_file'] = 'required|file|extensions:geojson';
        } elseif (!$this->route('id')) {
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
            'deskripsi.string' => 'Deskripsi harus berupa text.',
            'klasifikasi_id' => 'Klasifikasi wajib diisi',
            'geojson_file.file' => 'geojson_file harus berupa file.',
            'geojson_file.mimes' => 'geojson_file harus berformat geojson.',
            'warna.required' => 'Warna wajib diisi.',
            'warna.string' => 'Warna harus berupa teks.',
        ];
    }
}
