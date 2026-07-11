<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RaporAkhirDetailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kelas_id' => ['required', 'exists:kelas,id'],
            'siswa_id' => ['required', 'exists:siswas,id'],
            'tema_id' => ['required', 'exists:temas,id'],
            'thn_ajaran' => ['required', 'digits:4'],
            'keterangan' => ['required', 'string', 'max:5000'],
        ];
    }
}
