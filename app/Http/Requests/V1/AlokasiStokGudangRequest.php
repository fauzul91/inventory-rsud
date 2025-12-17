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
            'detailPemesanan' => ['required', 'array', 'min:1'],

            'detailPemesanan.*.detail_id' => [
                'required',
                'integer',
                'exists:detail_pemesanans,id'
            ],

            'detailPemesanan.*.quantity_admin' => [
                'required',
                'integer',
                'min:1'
            ],

            'detailPemesanan.*.allocations' => [
                'required',
                'array',
                'min:1'
            ],

            'detailPemesanan.*.allocations.*.detail_penerimaan_id' => [
                'required',
                'integer',
                'exists:detail_penerimaan_barangs,id'
            ],

            'detailPemesanan.*.allocations.*.quantity' => [
                'required',
                'integer',
                'min:1'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'detailPemesanan.required' => 'Detail pemesanan wajib diisi',
            'detailPemesanan.array' => 'Format detail tidak valid',

            'detailPemesanan.*.detail_id.required' => 'Detail pemesanan wajib dipilih',
            'detailPemesanan.*.detail_id.exists' => 'Detail pemesanan tidak ditemukan',

            'detailPemesanan.*.quantity_admin.required' => 'Quantity admin gudang wajib diisi',
            'detailPemesanan.*.quantity_admin.min' => 'Quantity admin gudang minimal 1',

            'detailPemesanan.*.allocations.required' => 'Alokasi stok wajib diisi',
            'detailPemesanan.*.allocations.array' => 'Format alokasi tidak valid',

            'detailPemesanan.*.allocations.*.detail_penerimaan_id.exists'
            => 'Data BAST tidak ditemukan',

            'detailPemesanan.*.allocations.*.quantity.min'
            => 'Quantity alokasi minimal 1',
        ];
    }
}
