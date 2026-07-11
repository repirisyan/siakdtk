<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TemaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Adjust authorization logic if needed
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nama_tema' => ['required', 'string', 'max:255'],
        ];
    }
}
