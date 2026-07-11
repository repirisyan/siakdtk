<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AbsenRequest extends FormRequest
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
            'siswa_id' => ['required', 'integer', 'exists:siswas,id'],
            'status' => ['required', 'in:hadir,izin,sakit,alfa'],
            'keterangan' => ['nullable', 'string', 'required_if:status,izin,sakit,alfa'],
        ];
    }
}
