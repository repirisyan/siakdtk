<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class KontenGaleriRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user instanceof User
            && ($user->hasRole('Admin') || $user->hasRole('Staff Administrasi'));
    }

    public function rules(): array
    {
        return [
            'gambar' => ['required', 'array', 'min:1'],
            'gambar.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'caption' => ['nullable', 'array'],
            'caption.*' => ['nullable', 'string', 'max:255'],
            'urutan' => ['nullable', 'array'],
            'urutan.*' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
