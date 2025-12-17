<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class AlokasiStokGudangRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'details' => ['required', 'array', 'min:1'],

            'details.*.detail_id' => [
                'required',
                'integer',
                'exists:detail_pemesanan,id'
            ],

            'details.*.quantity_admin' => [
                'required',
                'integer',
                'min:1'
            ],

            'details.*.allocations' => [
                'required',
                'array',
                'min:1'
            ],

            'details.*.allocations.*.detail_penerimaan_id' => [
                'required',
                'integer',
                'exists:detail_penerimaan_barang,id'
            ],

            'details.*.allocations.*.quantity' => [
                'required',
                'integer',
                'min:1'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'details.required' => 'Detail pemesanan wajib diisi',
            'details.array' => 'Format detail tidak valid',

            'details.*.detail_id.required' => 'Detail pemesanan wajib dipilih',
            'details.*.detail_id.exists' => 'Detail pemesanan tidak ditemukan',

            'details.*.quantity_admin.required' => 'Quantity admin gudang wajib diisi',
            'details.*.quantity_admin.min' => 'Quantity admin gudang minimal 1',

            'details.*.allocations.required' => 'Alokasi stok wajib diisi',
            'details.*.allocations.array' => 'Format alokasi tidak valid',

            'details.*.allocations.*.detail_penerimaan_id.exists'
            => 'Data BAST tidak ditemukan',

            'details.*.allocations.*.quantity.min'
            => 'Quantity alokasi minimal 1',
        ];
    }
}
