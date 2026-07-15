<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RaporRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user) {
            return false;
        }

        $user->loadMissing(['role', 'guru']);

        return $user->hasRole('Guru') && (bool) $user->guru;
    }

    public function rules(): array
    {
        return [
            'kelas_id' => ['required', 'integer', 'exists:kelas,id'],
            'siswa_id' => ['required', 'integer', 'exists:siswas,id'],
            'tema_id' => ['required', 'integer', 'exists:temas,id'],
            'sub_tema_id' => ['required', 'integer', 'exists:sub_temas,id'],
            'guru_id' => ['nullable', 'integer'],
            'thn_ajaran' => ['required', 'digits:4'],
            'keterangan' => ['required', 'string', 'max:5000'],
        ];
    }
}
