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
            'no_surat' => 'required|string|max:100|unique:penerimaans,no_surat',
            'category_id' => 'required|exists:categories,id',
            'deskripsi' => 'nullable|string',

            'detail_barangs' => 'required|array|min:1',
            'detail_barangs.*.nama_barang' => 'required|string|max:255',
            'detail_barangs.*.satuan_id' => 'required|exists:satuans,id',
            'detail_barangs.*.quantity' => 'required|numeric|min:1',
            'detail_barangs.*.harga' => 'required|numeric|min:0',

            'pegawais' => 'required|array|min:1',
            'pegawais.*.pegawai_id' => 'required|exists:pegawais,id',
            'pegawais.*.alamat_staker' => 'nullable|string|max:255',
        ];
    }
}
