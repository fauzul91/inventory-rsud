<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuantityPenanggungJawabRequest extends FormRequest
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
            'details' => ['required', 'array', 'min:1'],
            'details.*.detail_id' => ['required', 'integer', 'exists:detail_pemesanans,id'],
            'details.*.quantity_pj' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'details.required' => 'Detail pemesanan wajib diisi.',
            'details.array' => 'Format detail tidak valid.',
            'details.*.detail_id.required' => 'Detail ID wajib diisi.',
            'details.*.detail_id.exists' => 'Detail pemesanan tidak ditemukan.',
            'details.*.quantity_pj.required' => 'Quantity wajib diisi.',
            'details.*.quantity_pj.min' => 'Quantity minimal 1.',
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
