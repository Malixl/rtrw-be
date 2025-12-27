<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLayerGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation(): void
    {
        if ($this->has('layer_group_name')) {
            $this->merge(['nama_layer_group' => $this->input('layer_group_name')]);
        }
    }

    public function rules(): array
    {
        return [
            'layer_group_name' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'urutan_tampil' => 'nullable|integer',
        ];
    }
}
