<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class JenisPembayaranRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $user?->loadMissing('role');

        return (bool) ($user?->hasRole('Admin') || $user?->hasRole('Staff Administrasi'));
    }

    public function rules(): array
    {
        return [
            'nama_jenis' => ['required', 'string', 'max:100', Rule::unique('jenis_pembayarans', 'nama_jenis')->ignore($this->route('jenis_pembayaran'))],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
            'status' => ['nullable', 'boolean'],
        ];
    }
}
