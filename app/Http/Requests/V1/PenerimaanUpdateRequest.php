<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PenerimaanUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // PERBAIKAN: Gunakan route parameter yang benar
        // Cek dulu nama parameter di routes/api.php
        $penerimaanId = $this->route('penerimaan') ?? $this->route('id');
        
        return [
            'no_surat' => [
                'sometimes',
                'string',
                'max:100',
                // PERBAIKAN: Pastikan ignore menggunakan ID yang benar
                Rule::unique('penerimaans', 'no_surat')->ignore($penerimaanId, 'id'),
            ],
            'category_id' => 'sometimes|exists:categories,id',
            'deskripsi' => 'sometimes|nullable|string',
            'status' => 'sometimes|string|in:pending,confirmed,approved,rejected',

            // Detail Barang
            'detail_barangs' => 'sometimes|array',
            'detail_barangs.*.id' => 'nullable|exists:detail_penerimaan_barangs,id',
            'detail_barangs.*.stok_id' => 'required|exists:stoks,id',
            'detail_barangs.*.quantity' => 'required|numeric|min:1',
            'detail_barangs.*.harga' => 'sometimes|numeric|min:0',
            'detail_barangs.*.price' => 'sometimes|numeric|min:0',
            'detail_barangs.*.is_layak' => 'sometimes|nullable|boolean',

            'deleted_barang_ids' => 'sometimes|array',
            'deleted_barang_ids.*' => 'exists:detail_penerimaan_barangs,id',

            // Detail Pegawai
            'pegawais' => 'sometimes|array',
            'pegawais.*.pegawai_id' => 'required|exists:pegawais,id',
            'pegawais.*.alamat_staker' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'no_surat.unique' => 'Nomor surat sudah digunakan oleh penerimaan lain',
            'detail_barangs.*.stok_id.required' => 'Stok ID wajib diisi',
            'detail_barangs.*.quantity.required' => 'Quantity wajib diisi',
            'detail_barangs.*.quantity.min' => 'Quantity minimal 1',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('detail_barangs')) {
            $detailBarangs = $this->input('detail_barangs', []);
            
            foreach ($detailBarangs as $index => $barang) {
                if (isset($barang['price']) && !isset($barang['harga'])) {
                    $detailBarangs[$index]['harga'] = $barang['price'];
                }
            }
            
            $this->merge([
                'detail_barangs' => $detailBarangs
            ]);
        }
    }
}