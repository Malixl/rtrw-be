<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BatasAdministrasiRequest extends FormRequest
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
        $docRule = $this->isMethod('POST') ? 'required' : 'nullable';

        return [
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'geojson_file' => "$docRule|file|mimes:json,geojson",
            'warna' => 'nullable|string|max:20',
        ];
    }
}
