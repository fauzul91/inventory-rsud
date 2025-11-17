<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AccountUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('id');
        
        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],
            'photo' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,gif,webp',
                'max:2048', // 2MB max
            ],
            'role' => [
                'sometimes',
                'required',
                'string',
                'exists:roles,name',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi',
            'name.max' => 'Nama maksimal 255 karakter',
            'photo.image' => 'File harus berupa gambar',
            'photo.mimes' => 'Format gambar harus jpeg, png, jpg, gif, atau webp',
            'photo.max' => 'Ukuran gambar maksimal 2MB',
            'role.required' => 'Role wajib diisi',
            'role.exists' => 'Role tidak ditemukan',
        ];
    }
}