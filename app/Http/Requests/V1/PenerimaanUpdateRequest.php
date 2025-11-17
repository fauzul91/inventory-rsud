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
            'status' => 'sometimes|string|in:pending,approved,rejected', // opsional, kalau ada enum status

            // Barang
            'detail_barangs' => 'sometimes|array',
            'detail_barangs.*.id' => 'nullable|exists:detail_penerimaan_barangs,id',
            'detail_barangs.*.stok_id' => 'sometimes|exists:stoks,id', // bisa optional, kalau cuma update quantity/harga
            'detail_barangs.*.quantity' => 'sometimes|numeric|min:1',
            'detail_barangs.*.harga' => 'sometimes|numeric|min:0',
            'detail_barangs.*.is_layak' => 'sometimes|boolean',

            'deleted_barang_ids' => 'sometimes|array',
            'deleted_barang_ids.*' => 'exists:detail_penerimaan_barangs,id',

            // Pegawai
            'pegawais' => 'sometimes|array',
            'pegawais.*.pegawai_id' => 'required|exists:pegawais,id', // tetap wajib karena update existing
            'pegawais.*.alamat_staker' => 'nullable|string|max:255',
        ];
    }
}
