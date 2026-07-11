<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubTemaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['tema_id' => ['required', 'exists:temas,id'], 'nama_sub_tema' => ['required', 'string', 'max:255'], 'deskripsi' => ['nullable', 'string', 'max:2000']];
    }
}
