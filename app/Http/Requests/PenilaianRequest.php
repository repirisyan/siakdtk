<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PenilaianRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user) {
            return false;
        }

        $user->loadMissing(['role', 'guru']);

        return $user->hasRole('Admin')
            || $user->hasRole('Staff Akademik')
            || ($user->hasRole('Guru') && (bool) $user->guru);
    }

    public function rules(): array
    {
        return [
            'kelas_id' => ['required', 'integer', 'exists:kelas,id'],
            'jadwal_id' => ['required', 'integer', 'exists:jadwals,id'],
            'absen_id' => ['required', 'integer', 'exists:absens,id'],
            'komponen_penilaian_id' => ['required', 'integer', 'exists:komponen_penilaians,id'],
            'nilai' => ['required', 'string', 'max:5'],
            'keterangan' => ['nullable', 'string'],
        ];
    }
}
