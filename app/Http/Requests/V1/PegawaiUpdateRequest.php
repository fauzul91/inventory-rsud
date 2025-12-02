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
