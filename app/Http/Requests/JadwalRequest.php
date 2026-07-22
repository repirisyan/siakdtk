<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class JadwalRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user) {
            return false;
        }

        $user->loadMissing(['role', 'guru']);

        return in_array($user->role?->role_name, ['Admin', 'Staff Akademik'], true)
            || ($user->role?->role_name === 'Guru' && (bool) $user->guru);
    }

    public function rules(): array
    {
        return [
            'kelas_id' => ['required', 'integer', Rule::exists('kelas', 'id')->where('status', true)],
            'guru_id' => in_array($this->user()->role?->role_name, ['Admin', 'Staff Akademik'], true)
                ? ['required', 'integer', 'exists:gurus,id']
                : ['nullable', 'integer'],
            'tema_id' => ['required', 'integer', Rule::exists('temas', 'id')->where('status', true)],
            'tanggal' => ['required', 'date'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'jumlah_hari' => ['nullable', 'integer', 'min:1', 'max:31'],
            'skip_sabtu' => ['nullable', 'boolean'],
            'skip_minggu' => ['nullable', 'boolean'],
        ];
    }
}
