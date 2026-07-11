<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendSppNotificationByFilterRequest extends FormRequest
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
            'search' => ['nullable', 'string', 'max:255'],
            'kelas_id' => ['nullable', 'integer', 'exists:kelas,id'],
            'thn_ajaran' => ['nullable', 'digits:4'],
            'status' => ['nullable', 'in:lunas,belum_lunas'],
            'jenis_pembayaran' => ['nullable', 'string', 'max:100'],
        ];
    }
}
