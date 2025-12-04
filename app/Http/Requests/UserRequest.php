<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
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
        $userId = $this->route('user'); // untuk apiResource, parameter name adalah 'user'

        $rules = [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($userId)
            ],
            'role' => 'required|in:admin,opd',
        ];

        // Password required on create, optional on update
        if ($this->isMethod('post') && !$userId) {
            $rules['password'] = 'required|string|min:6';
            $rules['password_confirmation'] = 'required|same:password';
        } else {
            $rules['password'] = 'nullable|string|min:6';
            $rules['password_confirmation'] = 'nullable|same:password';
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi',
            'name.max' => 'Nama maksimal 255 karakter',
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah digunakan',
            'password.required' => 'Password wajib diisi',
            'password.min' => 'Password minimal 6 karakter',
            'password_confirmation.required' => 'Konfirmasi password wajib diisi',
            'password_confirmation.same' => 'Konfirmasi password tidak cocok',
            'role.required' => 'Role wajib dipilih',
            'role.in' => 'Role harus admin atau opd',
        ];
    }
}
