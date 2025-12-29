<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class PegawaiUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('pegawai');

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'nip' => [
                'sometimes',
                'string',
                'max:50',
                Rule::unique('pegawais', 'nip')->ignore($id),
            ],
            'jabatan_id' => ['sometimes', 'integer', 'exists:jabatans,id'],
            'phone' => ['sometimes', 'string', 'max:20'],
            'status' => ['sometimes', 'in:active,inactive'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.string' => 'Nama pegawai harus berupa teks.',
            'name.max' => 'Nama pegawai maksimal 255 karakter.',

            'nip.string' => 'NIP harus berupa teks.',
            'nip.max' => 'NIP maksimal 50 karakter.',
            'nip.unique' => 'NIP sudah digunakan pegawai lain.',

            'jabatan_id.integer' => 'Jabatan tidak valid.',
            'jabatan_id.exists' => 'Jabatan tidak ditemukan.',

            'phone.string' => 'Nomor telepon harus berupa teks.',
            'phone.max' => 'Nomor telepon maksimal 20 karakter.',

            'status.in' => 'Status pegawai harus bernilai active atau inactive.',
        ];
    }
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
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
