<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportSiswaRequest extends FormRequest
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
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
        ];
    }
}
