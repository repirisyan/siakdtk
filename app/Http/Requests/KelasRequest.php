<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KelasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_kelas' => ['required', 'string', 'max:255'],
            'tahun_ajaran_id' => ['required', 'integer', Rule::exists('tahun_ajarans', 'id')],
            'semester' => ['required', 'integer', 'in:1,2'],
        ];
    }
}
