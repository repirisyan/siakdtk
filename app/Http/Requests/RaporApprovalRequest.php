<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RaporApprovalRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user) {
            return false;
        }

        $user->loadMissing('role');

        return $user->hasRole('Kepsek');
    }

    public function rules(): array
    {
        return [
            'kelas_id' => ['required', 'integer', 'exists:kelas,id'],
            'thn_ajaran' => ['required', 'digits:4'],
        ];
    }
}
