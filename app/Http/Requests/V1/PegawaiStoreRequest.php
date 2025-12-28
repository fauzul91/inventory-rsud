<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class PegawaiStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => 'required|string|max:255',
            'nip'         => 'required|string|max:50|unique:pegawais,nip',
            'jabatan_id'  => 'required|integer|exists:jabatans,id',
            'phone'       => 'nullable|string|max:20',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'       => 'Nama pegawai wajib diisi.',
            'name.string'         => 'Nama pegawai harus berupa teks.',
            'name.max'            => 'Nama pegawai maksimal 255 karakter.',

            'nip.required'        => 'NIP wajib diisi.',
            'nip.string'          => 'NIP harus berupa teks.',
            'nip.max'             => 'NIP maksimal 50 karakter.',
            'nip.unique'          => 'NIP sudah terdaftar.',

            'jabatan_id.required' => 'Jabatan wajib dipilih.',
            'jabatan_id.integer'  => 'Jabatan tidak valid.',
            'jabatan_id.exists'   => 'Jabatan tidak ditemukan.',

            'phone.string'        => 'Nomor telepon harus berupa teks.',
            'phone.max'           => 'Nomor telepon maksimal 20 karakter.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name'       => 'Nama Pegawai',
            'nip'        => 'NIP',
            'jabatan_id' => 'Jabatan',
            'phone'      => 'Nomor Telepon',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}
