<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TahunAjaranRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $user?->loadMissing('role');

        return (bool) ($user?->hasRole('Admin') || $user?->hasRole('Staff Akademik'));
    }

    public function rules(): array
    {
        return [
            'tahun_ajaran' => [
                'required',
                'digits:4',
                Rule::unique('tahun_ajarans', 'tahun_ajaran')->ignore($this->route('tahunAjaran')),
            ],
        ];
    }
}
