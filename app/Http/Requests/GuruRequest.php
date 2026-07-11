<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GuruRequest extends FormRequest
{
    private const AGAMA_OPTIONS = [
        'Islam',
        'Kristen',
        'Katolik',
        'Hindu',
        'Buddha',
        'Konghucu',
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $guru = $this->route('guru');

        return [
            'name' => ['required', 'string', 'max:255'],
            'user_email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($guru?->user_id)],
            'password' => [$guru ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            'nama' => ['required', 'string', 'max:255'],
            'nip' => ['required', 'string', 'max:20', Rule::unique('gurus', 'nip')->ignore($guru)],
            'tmp_lhr' => ['required', 'string', 'max:255'],
            'tgl_lahir' => ['required', 'date'],
            'alamat' => ['required', 'string', 'max:1000'],
            'agama' => ['nullable', 'string', Rule::in(self::AGAMA_OPTIONS)],
            'nohp_guru' => ['nullable', 'string', 'max:255', Rule::unique('gurus', 'nohp_guru')->ignore($guru)],
            'email' => ['nullable', 'string', 'email', 'max:255', Rule::unique('gurus', 'email')->ignore($guru)],
            'jk' => ['nullable', 'string', 'max:20'],
            'jenis_ptk' => ['nullable', 'string', 'max:255'],
            'nuptk' => ['nullable', 'string', 'max:255'],
            'pendidikan' => ['nullable', 'string', 'max:255'],
            'stts_kepegawaian' => ['nullable', 'string', 'max:255'],
            'sk_cpns' => ['nullable', 'string', 'max:255'],
            'tgl_cpns' => ['nullable', 'string', 'max:255'],
            'sk_pengangkatan' => ['nullable', 'string', 'max:255'],
            'tmt_pengangkatan' => ['nullable', 'string', 'max:255'],
            'pangkat_golongan' => ['nullable', 'string', 'max:255'],
            'tmt_pns' => ['nullable', 'string', 'max:255'],
            'npwp' => ['nullable', 'string', 'max:255', Rule::unique('gurus', 'npwp')->ignore($guru)],
            'foto' => ['nullable', 'string', 'max:255'],
        ];
    }
}
