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
            // Deskripsi wajib diisi menurut spesifikasi admin
            'deskripsi' => 'required|string',

            // Layer group harus dipilih saat membuat (required on POST) OR layer_group_id boleh dipakai
            // required_without:layer_group_id => layer_group wajib jika layer_group_id tidak dikirim
            'layer_group' => ($this->isMethod('post') ? 'required_without:layer_group_id' : 'nullable') . '|string|exists:layer_groups,nama_layer_group',
            'layer_group_id' => 'nullable|exists:layer_groups,id',

            'tipe' => 'required|in:pola_ruang,struktur_ruang,ketentuan_khusus,indikasi_program,pkkprl,data_spasial',
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama wajib diisi.',
            'nama.string' => 'Nama harus berupa teks.',
            'deskripsi.required' => 'Deskripsi wajib diisi.',
            'deskripsi.string' => 'Deskripsi harus berupa teks.',

            'layer_group.required_without' => 'Layer Group wajib dipilih.',
            'layer_group.required' => 'Layer Group wajib dipilih.',
            'layer_group.exists' => 'Layer Group yang dipilih tidak valid.',
            'layer_group_id.exists' => 'Layer Group yang dipilih tidak valid.',

            'tipe.required' => 'Tipe klasifikasi wajib dipilih.',
            'tipe.in' => 'Tipe klasifikasi harus pola_ruang, struktur ruang, ketentuan khusus, indikasi program, pkkprl, atau data_spasial.',
        ];
    }
}
