<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class PenerimaanStoreRequest extends FormRequest
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
            'no_surat' => 'required|string',
            'category_id' => 'required|integer',
            'deskripsi' => 'required|string',
            'detail_barangs' => 'array|required',
            'detail_barangs.*.stok_id' => 'required_without:detail_barangs.*.name|nullable|integer',
            'detail_barangs.*.name' => 'required_without:detail_barangs.*.stok_id|nullable|string',
            'detail_barangs.*.quantity' => 'required|integer|min:1',
            'detail_barangs.*.harga' => 'required|numeric|min:0',
            'detail_barangs.*.category_id' => 'nullable|integer',
            'detail_barangs.*.category_name' => 'nullable|string',
            'detail_barangs.*.satuan_id' => 'nullable|integer',
            'detail_barangs.*.minimum_stok' => 'nullable|integer',
            'pegawais' => 'array|nullable',
            'pegawais.*.pegawai_id' => 'required|integer',
            'pegawais.*.alamat_staker' => 'required|string',
        ];
    }
}
