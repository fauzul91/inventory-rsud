<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class PegawaiUpdateRequest extends FormRequest
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
        $id = $this->route('id');

        return [
            'name' => 'sometimes|string|max:255',
            'nip' => 'sometimes|string|max:50|unique:pegawais,nip,' . $this->pegawai,
            'jabatan_id' => 'sometimes|integer|exists:jabatans,id',
            'phone' => 'sometimes|string|max:20',
            'status' => 'sometimes|in:active,inactive',
        ];
    }
    public function messages(): array
    {
        return [
            'name.string' => 'Atribut :attribute harus berupa format teks.',
            'name.max' => 'Atribut :attribute tidak boleh lebih dari :max karakter.',
            'nip.string' => 'Atribut :attribute harus berupa format teks.',
            'nip.max' => 'Atribut :attribute tidak boleh lebih dari :max karakter.',
            'nip.unique' => ':attribute sudah terdaftar dalam sistem.',
            'jabatan_id.integer' => 'Pilihan :attribute harus berupa angka.',
            'jabatan_id.exists' => ':attribute yang dipilih tidak valid atau tidak ditemukan.',
            'phone.string' => 'Atribut :attribute harus berupa format teks.',
            'phone.max' => 'Atribut :attribute tidak boleh lebih dari :max karakter.',
            'status.in' => ':attribute yang dipilih harus bernilai active atau inactive.',
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
