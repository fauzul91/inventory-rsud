<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class BastUploadRequest extends FormRequest
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
            'uploaded_signed_file' => 'required|file|mimes:pdf|max:2048'
        ];
    }
    public function messages(): array
    {
        return [
            'uploaded_signed_file.required' => 'File BAST wajib diupload',
            'uploaded_signed_file.mimes' => 'File harus berupa PDF',
            'uploaded_signed_file.max' => 'Ukuran file maksimal 2 MB',
        ];
    }
}
