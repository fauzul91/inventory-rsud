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
            'no_surat' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'category_name' => ['nullable', 'string', 'max:255'],
            'deskripsi' => ['required', 'string'],

            'detail_barangs' => ['required', 'array', 'min:1'],
            'detail_barangs.*.stok_id' => ['required_without:detail_barangs.*.name', 'nullable', 'integer', 'exists:stoks,id'],
            'detail_barangs.*.name' => ['required_without:detail_barangs.*.stok_id', 'nullable', 'string', 'max:255'],
            'detail_barangs.*.quantity' => ['required', 'integer', 'min:1'],
            'detail_barangs.*.harga' => ['required', 'numeric', 'min:0'],
            'detail_barangs.*.satuan_id' => ['nullable', 'integer', 'exists:satuans,id'],
            'detail_barangs.*.satuan_name' => ['nullable', 'string', 'max:255'],
            'detail_barangs.*.minimum_stok' => ['nullable', 'integer', 'min:0'],

            'pegawais' => ['required', 'array', 'min:2'],
            'pegawais.*.pegawai_id' => ['required', 'integer', 'exists:pegawais,id', 'distinct'],
            'pegawais.*.alamat_staker' => ['required', 'string', 'max:500'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'no_surat.required' => 'Nomor surat wajib diisi',
            'no_surat.string' => 'Nomor surat harus berupa teks',
            'no_surat.max' => 'Nomor surat maksimal 255 karakter',

            'category_id.integer' => 'Category ID harus berupa angka',
            'category_id.exists' => 'Category tidak ditemukan',
            'category_name.string' => 'Nama category harus berupa teks',
            'category_name.max' => 'Nama category maksimal 255 karakter',

            'deskripsi.required' => 'Deskripsi wajib diisi',
            'deskripsi.string' => 'Deskripsi harus berupa teks',

            'detail_barangs.required' => 'Detail barang wajib diisi',
            'detail_barangs.array' => 'Detail barang harus berupa array',
            'detail_barangs.min' => 'Minimal harus ada 1 barang',

            'detail_barangs.*.stok_id.required_without' => 'Stok ID wajib diisi jika tidak menyertakan nama barang',
            'detail_barangs.*.stok_id.integer' => 'Stok ID harus berupa angka',
            'detail_barangs.*.stok_id.exists' => 'Stok tidak ditemukan',

            'detail_barangs.*.name.required_without' => 'Nama barang wajib diisi jika tidak menyertakan stok ID',
            'detail_barangs.*.name.string' => 'Nama barang harus berupa teks',
            'detail_barangs.*.name.max' => 'Nama barang maksimal 255 karakter',

            'detail_barangs.*.quantity.required' => 'Quantity wajib diisi',
            'detail_barangs.*.quantity.integer' => 'Quantity harus berupa angka',
            'detail_barangs.*.quantity.min' => 'Quantity minimal 1',

            'detail_barangs.*.harga.required' => 'Harga wajib diisi',
            'detail_barangs.*.harga.numeric' => 'Harga harus berupa angka',
            'detail_barangs.*.harga.min' => 'Harga tidak boleh negatif',

            'detail_barangs.*.satuan_id.integer' => 'Satuan ID harus berupa angka',
            'detail_barangs.*.satuan_id.exists' => 'Satuan tidak ditemukan',
            'detail_barangs.*.satuan_name.string' => 'Nama satuan harus berupa teks',
            'detail_barangs.*.satuan_name.max' => 'Nama satuan maksimal 255 karakter',

            'detail_barangs.*.minimum_stok.integer' => 'Minimum stok harus berupa angka',
            'detail_barangs.*.minimum_stok.min' => 'Minimum stok tidak boleh negatif',

            'pegawais.required' => 'Pegawai wajib diisi',
            'pegawais.array' => 'Pegawai harus berupa array',
            'pegawais.min' => 'Minimal harus ada 2 pegawai',

            'pegawais.*.pegawai_id.required' => 'Pegawai ID wajib diisi',
            'pegawais.*.pegawai_id.integer' => 'Pegawai ID harus berupa angka',
            'pegawais.*.pegawai_id.exists' => 'Pegawai tidak ditemukan',
            'pegawais.*.pegawai_id.distinct' => 'Tidak boleh ada pegawai yang sama (duplikat)',

            'pegawais.*.alamat_staker.required' => 'Alamat staker wajib diisi',
            'pegawais.*.alamat_staker.string' => 'Alamat staker harus berupa teks',
            'pegawais.*.alamat_staker.max' => 'Alamat staker maksimal 500 karakter',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'no_surat' => 'Nomor Surat',
            'category_id' => 'Category ID',
            'category_name' => 'Nama Category',
            'deskripsi' => 'Deskripsi',
            'detail_barangs' => 'Detail Barang',
            'detail_barangs.*.stok_id' => 'Stok ID',
            'detail_barangs.*.name' => 'Nama Barang',
            'detail_barangs.*.quantity' => 'Quantity',
            'detail_barangs.*.harga' => 'Harga',
            'detail_barangs.*.satuan_id' => 'Satuan ID',
            'detail_barangs.*.satuan_name' => 'Nama Satuan',
            'detail_barangs.*.minimum_stok' => 'Minimum Stok',
            'pegawais' => 'Pegawai',
            'pegawais.*.pegawai_id' => 'Pegawai ID',
            'pegawais.*.alamat_staker' => 'Alamat Staker',
        ];
    }

    /**
     * Prepare data for validation
     */
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

    /**
     * Custom validation after default validation
     */
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
}