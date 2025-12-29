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

    public function messages(): array
    {
        return [
            'nama_pj_instalasi.required' => 'Nama penanggung jawab instalasi wajib diisi.',
            'nama_pj_instalasi.string' => 'Nama penanggung jawab instalasi harus berupa teks.',

            'ruangan.required' => 'Ruangan wajib diisi.',
            'ruangan.string' => 'Ruangan harus berupa teks.',

            'items.required' => 'Item pemesanan wajib diisi.',
            'items.array' => 'Format item pemesanan tidak valid.',
            'items.min' => 'Minimal harus ada 1 item pemesanan.',

            'items.*.stok_id.required' => 'Stok wajib dipilih.',
            'items.*.stok_id.integer' => 'Stok ID harus berupa angka.',
            'items.*.stok_id.exists' => 'Stok yang dipilih tidak ditemukan.',

            'items.*.quantity.required' => 'Jumlah item wajib diisi.',
            'items.*.quantity.integer' => 'Jumlah item harus berupa angka.',
            'items.*.quantity.min' => 'Jumlah item minimal 1.',
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new \Illuminate\Http\Exceptions\HttpResponseException(
            response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
                'data' => null,
            ], 422)
        );
    }
}
