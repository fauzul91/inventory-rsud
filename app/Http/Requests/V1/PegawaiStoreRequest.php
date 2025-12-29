<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class PegawaiStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'nip' => 'required|string|max:50|unique:pegawais,nip',
            'jabatan_id' => 'required|integer|exists:jabatans,id',
            'phone' => 'nullable|string|max:20',
            'status' => 'required|in:active,inactive',
        ];
    }
    public function messages(): array
    {
        return [
            'name.required'      => 'Atribut Nama Pegawai wajib diisi.',
            'name.string'        => 'Atribut Nama Pegawai harus berupa format teks.',
            'name.max'           => 'Atribut Nama Pegawai tidak boleh lebih dari 255 karakter.',

            'nip.required'       => 'Atribut NIP wajib diisi.',
            'nip.string'         => 'Atribut NIP harus berupa format teks.',
            'nip.max'            => 'Atribut NIP tidak boleh lebih dari 50 karakter.',
            'nip.unique'         => 'NIP tersebut sudah digunakan oleh pegawai lain.',

            'jabatan_id.required' => 'Bidang Jabatan wajib dipilih.',
            'jabatan_id.integer'  => 'Format pilihan Jabatan tidak valid.',
            'jabatan_id.exists'   => 'Jabatan yang dipilih tidak ditemukan dalam sistem.',

            'phone.string'       => 'Atribut Nomor Telepon harus berupa format teks.',
            'phone.max'          => 'Atribut Nomor Telepon tidak boleh lebih dari 20 karakter.',

            'status.required'    => 'Atribut Status Pegawai wajib ditentukan.',
            'status.in'          => 'Status yang dipilih harus salah satu dari: active atau inactive.',
        ];
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

    public function attributes(): array
    {
        return [
            'name' => 'Nama Pegawai',
            'nip' => 'NIP',
            'jabatan_id' => 'Jabatan',
            'phone' => 'Nomor Telepon',
            'status' => 'Status Pegawai',
        ];
    }
}
