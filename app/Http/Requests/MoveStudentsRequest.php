<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MoveStudentsRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $kelas = $this->route('kelas');

        $this->merge([
            'kelas_id' => $kelas?->id,
        ]);
    }

    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user) {
            return false;
        }

        $user->loadMissing('role');

        return $user->hasRole('Admin');
    }

    public function rules(): array
    {
        return [
            'kelas_id' => ['required', 'integer', 'exists:kelas,id'],
            'kelas_tujuan_id' => ['required', 'integer', 'exists:kelas,id', 'different:kelas_id'],
        ];
    }
}
