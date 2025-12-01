<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RtrwRequest extends FormRequest
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
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'periode_id' => 'required|exists:periode,id',
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama RTRW wajib diisi.',
            'nama.string' => 'Nama RTRW harus berupa teks.',
            'nama.max' => 'Nama RTRW maksimal 255 karakter.',


            'periode_id.required' => 'Periode wajib diisi.',
            'periode_id.exists' => 'Periode yang dipilih tidak ditemukan.',

            'deskripsi.string' => 'Deskripsi harus berupa teks.',
        ];
    }
}
