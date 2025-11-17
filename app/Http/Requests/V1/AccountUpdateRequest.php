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
        return [
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'name' => 'sometimes|string|max:255',
            'role' => 'sometimes|string|exists:roles,name',
        ];
    }

    public function messages(): array
    {
        return [
            'role.*.exists' => 'Role yang dipilih tidak valid.',
        ];
    }
}
