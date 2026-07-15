<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KomponenPenilaianRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $komponen = $this->route('komponenPenilaian');

        return [
            'sub_tema_id' => ['required', 'integer', 'exists:sub_temas,id'],
            'nama_komponen' => [
                'required', 'string', 'max:255',
                Rule::unique('komponen_penilaians', 'nama_komponen')
                    ->where('sub_tema_id', $this->input('sub_tema_id'))
                    ->ignore($komponen),
            ],
            'deskripsi' => ['nullable', 'string', 'max:5000'],
            'status' => ['nullable', 'boolean'],
        ];
    }
}
