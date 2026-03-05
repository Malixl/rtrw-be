<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DokumenRequest extends FormRequest
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
            'nama' => 'required|string|max:255',
            'klasifikasi_id' => 'required',
        ];

        // Validasi file hanya jika ada file yang diupload atau ini adalah request create
        if ($this->hasFile('file_dokumen')) {
            $rules['file_dokumen'] = 'required|file|mimes:pdf';
        } elseif (!$this->route('id')) {
            // Jika create (tidak ada ID), file wajib
            $rules['file_dokumen'] = 'required|file|mimes:pdf';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'file_dokumen.required' => 'Dokumen wajib diisi.',
            'file_dokumen.file' => 'Dokumen harus berupa file.',
            'file_dokumen.mimes' => 'Dokumen hanya boleh berupa file PDF',
        ];
    }
}
