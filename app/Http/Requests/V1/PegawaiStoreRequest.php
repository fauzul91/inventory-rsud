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
