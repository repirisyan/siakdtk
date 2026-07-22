<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MasterKomponenPenilaianRequest extends FormRequest
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
            'nama_komponen' => [
                'required',
                'string',
                'max:255',
                Rule::unique('master_komponen_penilaians', 'nama_komponen')
                    ->ignore($this->route('masterKomponenPenilaian')),
            ],
            'deskripsi' => ['nullable', 'string', 'max:2000'],
            'status' => ['nullable', 'boolean'],
        ];
    }
}
