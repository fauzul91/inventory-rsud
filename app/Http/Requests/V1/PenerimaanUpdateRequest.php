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
        return [
            'no_surat' => ['sometimes', 'string', 'max:255'],
            'deskripsi' => ['sometimes', 'nullable', 'string'],
            'status' => ['sometimes', 'string', Rule::in(['pending'])],
            'category_id' => ['sometimes', 'integer', 'exists:categories,id'],
            'category_name' => ['sometimes', 'string', 'max:255'],
            'detail_barangs' => ['sometimes', 'array'],
            'detail_barangs.*.id' => ['sometimes', 'integer', 'exists:detail_penerimaan_barangs,id'],
            'detail_barangs.*.stok_id' => [
                'sometimes',
                'integer',
                'exists:stoks,id',
                Rule::requiredIf(fn() => !$this->hasNameForNewItem())
            ],
            'detail_barangs.*.name' => ['sometimes', 'string', 'max:255'],
            'detail_barangs.*.quantity' => ['required_with:detail_barangs', 'integer', 'min:1'],
            'detail_barangs.*.harga' => ['sometimes', 'numeric', 'min:0'],
            'detail_barangs.*.price' => ['sometimes', 'numeric', 'min:0'],
            'detail_barangs.*.minimum_stok' => ['sometimes', 'integer', 'min:0'],
            'detail_barangs.*.satuan_id' => ['sometimes', 'integer', 'exists:satuans,id'],
            'detail_barangs.*.satuan_name' => ['sometimes', 'string', 'max:255'],

            'pegawais' => ['sometimes', 'array', 'min:2'],
            'pegawais.*.id' => ['sometimes', 'integer', 'exists:detail_penerimaan_pegawais,id'],
            'pegawais.*.pegawai_id' => ['required_with:pegawais', 'integer', 'exists:pegawais,id', 'distinct'],
            'pegawais.*.alamat_staker' => ['required', 'nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'no_surat.unique' => 'Nomor surat sudah digunakan oleh penerimaan lain',
            'status.in' => 'Status harus pending',

            'detail_barangs.*.stok_id.required' => 'Stok ID wajib diisi jika tidak menyertakan nama barang',
            'detail_barangs.*.stok_id.exists' => 'Stok tidak ditemukan',
            'detail_barangs.*.name.required' => 'Nama barang wajib diisi jika tidak menyertakan stok_id',
            'detail_barangs.*.quantity.required_with' => 'Quantity wajib diisi',
            'detail_barangs.*.quantity.min' => 'Quantity minimal 1',
            'detail_barangs.*.harga.min' => 'Harga tidak boleh negatif',

            'pegawais.*.pegawai_id.required_with' => 'Pegawai ID wajib diisi',
            'pegawais.*.pegawai_id.exists' => 'Pegawai tidak ditemukan',
            'pegawais.*.pegawai_id.distinct' => 'Pegawai tidak boleh duplikat',
            'pegawais.min' => 'Minimal harus ada 2 pegawai',
            'pegawais.*.alamat_staker.required' => 'Alamat satker wajib diisi',
        ];
    }

    protected function prepareForValidation(): void
    {
        $merge = [];

        if ($this->has('detail_barangs')) {
            $detailBarangs = $this->input('detail_barangs', []);

            foreach ($detailBarangs as $index => $barang) {
                if (isset($barang['price']) && !isset($barang['harga'])) {
                    $detailBarangs[$index]['harga'] = $barang['price'];
                }

                if (isset($barang['name'])) {
                    $detailBarangs[$index]['name'] = trim($barang['name']);
                }

                if (isset($barang['satuan_name'])) {
                    $detailBarangs[$index]['satuan_name'] = trim($barang['satuan_name']);
                }
            }

            $merge['detail_barangs'] = $detailBarangs;
        }

        if ($this->has('pegawais')) {
            $pegawais = $this->input('pegawais', []);

            foreach ($pegawais as $index => $pegawai) {
                if (isset($pegawai['alamat_staker'])) {
                    $pegawais[$index]['alamat_staker'] = trim($pegawai['alamat_staker']);
                }
            }

            $merge['pegawais'] = $pegawais;
        }

        if (!empty($merge)) {
            $this->merge($merge);
        }
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->has('detail_barangs')) {
                foreach ($this->input('detail_barangs', []) as $index => $barang) {
                    if (empty($barang['stok_id']) && empty($barang['name'])) {
                        $validator->errors()->add(
                            "detail_barangs.{$index}",
                            'Setiap barang harus memiliki stok_id atau name'
                        );
                    }

                    if (
                        empty($barang['stok_id']) &&
                        empty($barang['satuan_id']) &&
                        empty($barang['satuan_name'])
                    ) {
                        $validator->errors()->add(
                            "detail_barangs.{$index}.satuan",
                            'Untuk barang baru, harus menyertakan satuan_id atau satuan_name'
                        );
                    }
                }
            }

            if ($this->has('pegawais')) {
                $pegawais = $this->input('pegawais', []);
                if (count($pegawais) < 2) {
                    $validator->errors()->add(
                        'pegawais',
                        'Minimal harus ada 2 pegawai'
                    );
                }

                $pegawaiIds = collect($pegawais)->pluck('pegawai_id')->toArray();
                if (count($pegawaiIds) !== count(array_unique($pegawaiIds))) {
                    $validator->errors()->add(
                        'pegawais',
                        'Tidak boleh ada pegawai yang sama (duplikat)'
                    );
                }
            }
        });
    }

    private function hasNameForNewItem(): bool
    {
        return true;
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