<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PeriodeRequest extends FormRequest
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
            'tahun_mulai' => 'required|digits:4|integer|min:1900|max:' . date('Y'),
            'tahun_akhir' => 'required|digits:4|integer|min:1900|gte:tahun_mulai',
        ];
    }

    public function messages(): array
    {
        return [
            'tahun_mulai.required' => 'Tahun mulai wajib diisi.',
            'tahun_mulai.digits' => 'Tahun mulai harus terdiri dari 4 digit.',
            'tahun_mulai.integer' => 'Tahun mulai harus berupa angka.',
            'tahun_mulai.min' => 'Tahun mulai tidak valid.',
            'tahun_mulai.max' => 'Tahun mulai tidak boleh melebihi tahun saat ini.',

            'tahun_akhir.required' => 'Tahun akhir wajib diisi.',
            'tahun_akhir.digits' => 'Tahun akhir harus terdiri dari 4 digit.',
            'tahun_akhir.integer' => 'Tahun akhir harus berupa angka.',
            'tahun_akhir.min' => 'Tahun akhir tidak valid.',
            'tahun_akhir.gte' => 'Tahun akhir tidak boleh lebih kecil dari tahun mulai.',
        ];
    }
}
