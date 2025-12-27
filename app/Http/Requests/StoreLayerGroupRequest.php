<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLayerGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation(): void
    {
        // Support raw JSON array sent as root by wrapping it into `data` key so rules apply.
        $json = $this->json()->all();

        if (is_array($json) && array_values($json) === $json) {
            // numeric-indexed array (root array)
            $this->merge(['data' => $json]);
        }

        // Accept `layer_group_name` as public field; map to internal DB column `nama_layer_group` so existing code keeps working.
        if ($this->has('layer_group_name')) {
            $this->merge(['nama_layer_group' => $this->input('layer_group_name')]);
        }
    }

    public function rules(): array
    {
        // If this request is used for bulk import (controller action `import`),
        // validate JSON array payload under `data` key to match Rafiq format.
        $action = $this->route() ? $this->route()->getActionMethod() : null;

        if ($action === 'import') {
            return [
                'data' => 'required|array|min:1',
                'data.*.layer_group_name' => 'required|string',
                'data.*.deskripsi' => 'nullable|string',
                'data.*.urutan_tampil' => 'nullable|integer',
                'data.*.klasifikasis' => 'nullable|array',
            ];
        }

        return [
            'layer_group_name' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'urutan_tampil' => 'nullable|integer',
        ];
    }
}
