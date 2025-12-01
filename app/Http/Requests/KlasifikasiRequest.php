<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class KlasifikasiRequest extends FormRequest
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
        return [
            'nama' => 'required|string',
            'deskripsi' => 'string',
            'rtrw_id' => 'required',
            'tipe' => 'required|in:pola_ruang,struktur_ruang,ketentuan_khusus,indikasi_program,pkkprl',
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama wajib diisi.',
            'nama.string' => 'Nama harus berupa teks.',
            'deskripsi.string' => 'Deskripsi harus berupa teks.',
            'rtrw_id.required' => 'RTRW wajib diisi.',
            'tipe.required' => 'Tipe klasifikasi wajib dipilih.',
            'tipe.in'       => 'Tipe klasifikasi harus pola_ruang, struktur ruang, ketentuan khusus, indikasi program, atau pkkprl.',
        ];
    }
}
