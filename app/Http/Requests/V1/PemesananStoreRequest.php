<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class PemesananStoreRequest extends FormRequest
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
            'nama_pj_instalasi' => 'required|string',
            'ruangan' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.stok_id' => 'required|integer|exists:stoks,id',
            'items.*.quantity' => 'required|integer|min:1',
        ];
    }
}
