<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLayerGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_layer_group' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'urutan_tampil' => 'nullable|integer',
        ];
    }
}
