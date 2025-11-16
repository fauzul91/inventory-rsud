<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class PenerimaanUpdateRequest extends FormRequest
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
            'no_surat' => 'sometimes|string|max:100|unique:penerimaans,no_surat,' . $this->route('id'),
            'category_id' => 'sometimes|exists:categories,id',
            'deskripsi' => 'sometimes|string',

            'detail_barangs' => 'sometimes|array',
            'detail_barangs.*.id' => 'nullable|exists:detail_penerimaan_barangs,id',
            'detail_barangs.*.stok_id' => 'required|exists:stoks,id', // <- ini wajib
            'detail_barangs.*.quantity' => 'required|numeric|min:1',

            'pegawais' => 'sometimes|array|min:1',
            'pegawais.*.pegawai_id' => 'required|exists:pegawais,id',
            'pegawais.*.alamat_staker' => 'nullable|string|max:255',
        ];
    }
}
